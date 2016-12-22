<?php

/**
 * Simple SQL authentication source
 *
 * This class is an example authentication source which authenticates an user
 * against a SQL database.
 *
 * @package SimpleSAMLphp
 */
class sspmod_sqlauth_Auth_Source_SQL extends sspmod_core_Auth_UserPassBase {


	/**
	 * The DSN we should connect to.
	 */
	private $dsn;


	/**
	 * The username we should connect to the database with.
	 */
	private $username;


	/**
	 * The password we should connect to the database with.
	 */
	private $password;


	/**
	 * The query we should use to retrieve the attributes for the user.
	 *
	 * The username and password will be available as :username and :password.
	 */
	private $query;

    /** @var int */
    private $maxFailedLoginAttempts;

    /** @var int */
    private $banPeriod;

	/**
	 * The query we should use to update failed login attempts count for the user.
	 *
	 * The username will be available as :username.
	 */
	private $failedLoginAttemptsQuery;

	/**
	 * The query we should use to update failed login attempts count for the user.
	 *
	 * The username and failedLoginAttempts will be available as :username and :failedLoginAttempts.
	 */
	private $failedLoginAttemptsUpdate;

	/**
     * The query we should use to update bannedAt field for the user.
     *
	 * The username and bannedAt will be available as :username and :bannedAt.
	 */
	private $bannedAtUpdate;


	/**
	 * Constructor for this authentication source.
	 *
	 * @param array $info  Information about this authentication source.
	 * @param array $config  Configuration.
	 */
	public function __construct($info, $config) {
		assert('is_array($info)');
		assert('is_array($config)');

		// Call the parent constructor first, as required by the interface
		parent::__construct($info, $config);

		// Make sure that all required parameters are present.
		foreach (array('dsn', 'username', 'password', 'query') as $param) {
			if (!array_key_exists($param, $config)) {
				throw new Exception('Missing required attribute \'' . $param .
					'\' for authentication source ' . $this->authId);
			}

			if (!is_string($config[$param])) {
				throw new Exception('Expected parameter \'' . $param .
					'\' for authentication source ' . $this->authId .
					' to be a string. Instead it was: ' .
					var_export($config[$param], TRUE));
			}
		}

		$this->dsn = $config['dsn'];
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->query = $config['query'];

		$this->maxFailedLoginAttempts = isset($config['maxFailedLoginAttempts']) ? $config['maxFailedLoginAttempts'] : false;

        if ($this->maxFailedLoginAttempts) {
            $this->banPeriod = $config['banPeriod'];
            $this->failedLoginAttemptsQuery = $config['failedLoginAttemptsQuery'];
            $this->failedLoginAttemptsUpdate = $config['failedLoginAttemptsUpdate'];
            $this->bannedAtUpdate = $config['bannedAtUpdate'];
        }
	}


	/**
	 * Create a database connection.
	 *
	 * @return PDO  The database connection.
	 */
	private function connect() {
		try {
			$db = new PDO($this->dsn, $this->username, $this->password);
		} catch (PDOException $e) {
			throw new Exception('sqlauth:' . $this->authId . ': - Failed to connect to \'' .
				$this->dsn . '\': '. $e->getMessage());
		}

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


		$driver = explode(':', $this->dsn, 2);
		$driver = strtolower($driver[0]);

		/* Driver specific initialization. */
		switch ($driver) {
		case 'mysql':
			/* Use UTF-8. */
			$db->exec("SET NAMES 'utf8'");
			break;
		case 'pgsql':
			/* Use UTF-8. */
			$db->exec("SET NAMES 'UTF8'");
			break;
		}

		return $db;
	}


	/**
	 * Attempt to log in using the given username and password.
	 *
	 * On a successful login, this function should return the users attributes. On failure,
	 * it should throw an exception. If the error was caused by the user entering the wrong
	 * username or password, a SimpleSAML_Error_Error('WRONGUSERPASS') should be thrown.
	 *
	 * Note that both the username and the password are UTF-8 encoded.
	 *
	 * @param string $username  The username the user wrote.
	 * @param string $password  The password the user wrote.
	 * @return array  Associative array with the users attributes.
	 */
	protected function login($username, $password) {
		assert('is_string($username)');
		assert('is_string($password)');

		$db = $this->connect();

		try {
			$sth = $db->prepare($this->query);
		} catch (PDOException $e) {
			throw new Exception('sqlauth:' . $this->authId .
				': - Failed to prepare query: ' . $e->getMessage());
		}

		try {
			$res = $sth->execute(array('username' => $username, 'password' => $password));
		} catch (PDOException $e) {
			throw new Exception('sqlauth:' . $this->authId .
				': - Failed to execute query: ' . $e->getMessage());
		}

		try {
			$data = $sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception('sqlauth:' . $this->authId .
				': - Failed to fetch result set: ' . $e->getMessage());
		}

		SimpleSAML\Logger::info('sqlauth:' . $this->authId . ': Got ' . count($data) .
			' rows from database');

		if (count($data) === 0) {
			/* No rows returned - invalid username/password. */
			SimpleSAML\Logger::error('sqlauth:' . $this->authId .
				': No rows in result set. Probably wrong username/password for user \'' . $username .'\'.');

            if ($this->maxFailedLoginAttempts) {
                $this->updateUserOnLoginFailed($username);
            }

			throw new SimpleSAML_Error_Error('WRONGUSERPASS');
		}

        if ($this->maxFailedLoginAttempts) {
            $this->updateUserOnLoginSuccess($username);
        }

		/* Extract attributes. We allow the resultset to consist of multiple rows. Attributes
		 * which are present in more than one row will become multivalued. NULL values and
		 * duplicate values will be skipped. All values will be converted to strings.
		 */
		$attributes = array();
		foreach ($data as $row) {
			foreach ($row as $name => $value) {

				if ($value === NULL) {
					continue;
				}

				$value = (string)$value;

				if (!array_key_exists($name, $attributes)) {
					$attributes[$name] = array();
				}

				if (in_array($value, $attributes[$name], TRUE)) {
					/* Value already exists in attribute. */
					continue;
				}

				$attributes[$name][] = $value;
			}
		}

		SimpleSAML\Logger::info('sqlauth:' . $this->authId . ': Attributes: ' .
			implode(',', array_keys($attributes)));

		return $attributes;
	}

    /**
     * @param string $username
     * @throws Exception
     */
    protected function updateUserOnLoginFailed($username)
    {
        $db = $this->connect();

        $failedLoginAttempts = $this->fetchFailedLoginAttempts($username);

        //Incorrect username
        if ($failedLoginAttempts === false) {

            return;
        }

        try {
            //Increase failedLoginAttempts
            $sth = $db->prepare($this->failedLoginAttemptsUpdate);
            $sth->execute(array('username' => $username, 'failedLoginAttempts' => ++$failedLoginAttempts));
        } catch (PDOException $e) {
            throw new Exception('sqlauth:' . $this->authId .
                ': - Failed to prepare query: ' . $e->getMessage());
        }

        //Set bannedAt if max loginAttempts reached
        try {
            if ($failedLoginAttempts == $this->maxFailedLoginAttempts) {
                $banDate = new DateTime();

                $sth = $db->prepare($this->bannedAtUpdate);
                $sth->execute(array('username' => $username, 'bannedAt' => $banDate->format('Y-m-d H:i:s')));

                $bannedUntil = $banDate->add(new DateInterval('PT' . $this->banPeriod . 'S'))->format('Y-m-d H:i:s');
                SimpleSAML\Logger::error('sqlauth:' . $this->authId .
                    ': Max login attempts reached, user banned. Login disabled until ' . $bannedUntil. ' for user \'' . $username . '\'.');
            }

            if ($failedLoginAttempts >= $this->maxFailedLoginAttempts) {

                throw new SimpleSAML_Error_Error('MAX_LOGIN_ATTEMPTS_REACHED');
            }
        } catch (PDOException $e) {
            throw new Exception('sqlauth:' . $this->authId .
                ': - Failed to prepare query: ' . $e->getMessage());
        }
    }

    /**
     * @param string $username
     * @throws Exception
     */
    protected function updateUserOnLoginSuccess($username)
    {
        $db = $this->connect();

        try {
            $sth = $db->prepare($this->failedLoginAttemptsUpdate);
            $sth->execute(array('username' => $username, 'failedLoginAttempts' => 0));

            $sth = $db->prepare($this->bannedAtUpdate);
            $sth->bindValue(":bannedAt", null, PDO::PARAM_NULL);
            $sth->bindValue(":username", $username);
            $sth->execute();
        } catch (PDOException $e) {

            throw new Exception('sqlauth:' . $this->authId .
                ': - Failed to prepare query: ' . $e->getMessage());
        }
    }

    /**
     * @param $username
     * @return int|false
     * @throws Exception
     */
    protected function fetchFailedLoginAttempts($username)
    {
        $db = $this->connect();

        $sth = $db->prepare($this->failedLoginAttemptsQuery);
        $sth->execute(array('username' => $username));

        $data = $sth->fetchAll(PDO::FETCH_ASSOC);

        //Incorrect username
        if (count($data) === 0) {

            return false;
        }

        if (!isset($data[0]['failedloginattempts'])) {

            throw new Exception('sqlauth:' . $this->authId .
                ': - Failed to get failedLoginAttempts for user: ' . $username);
        }

        return $data[0]['failedloginattempts'];
    }

}
