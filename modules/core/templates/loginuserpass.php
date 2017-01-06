<?php
$this->data['header'] = $this->t('{login:user_pass_header}');

if (strlen($this->data['username']) > 0) {
    $this->data['autofocus'] = 'password';
} else {
    $this->data['autofocus'] = 'username';
}
$this->includeAtTemplateBase('includes/header.php');

?>

<?php
if ($this->data['errorcode'] !== null) {
    ?>
    <div class="login-error animated flipInX">
        <img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png"
             class="float-l erroricon" alt=""/>

        <p><strong><?php
            echo htmlspecialchars($this->t($this->data['errorcodes']['title'][$this->data['errorcode']], $this->data['errorparams'])); ?></strong></p>

        <p><?php
            echo htmlspecialchars($this->t($this->data['errorcodes']['descr'][$this->data['errorcode']], $this->data['errorparams'])); ?></p>
    </div>
<?php
}

?>
    <h2 style="break: both">Bejelentkezés<span class="mobile-logo"><img src="/<?php echo $this->data['baseurlpath'] ?>resources/icons/atlassoft7a.png" alt="Atlas Soft" /></span></h2>

    <form action="?" method="post" name="f" class="login-form">
        <table>
            <tr>
                <td rowspan="2" id="loginicon">
                    <img alt=""
                        src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-authentication.48x48.png" />
                </td>
                <td><label for="username"><?php echo $this->t('{login:username}'); ?></label></td>
                <td>
                    <input id="username" <?php echo ($this->data['forceUsername']) ? 'disabled="disabled"' : ''; ?>
                           type="text" name="username"
<?php if (!$this->data['forceUsername']) {
    echo 'tabindex="1"';
} ?> value="<?php echo htmlspecialchars($this->data['username']); ?>"/>
                </td>
            <?php
            if ($this->data['rememberUsernameEnabled'] && !$this->data['forceUsername']) {
                // display the "remember my username" checkbox
            ?>
                <td id="regular_remember_username">
                    <input type="checkbox" id="remember_username" tabindex="4"
                           <?php echo ($this->data['rememberUsernameChecked']) ? 'checked="checked"' : ''; ?>
                           name="remember_username" value="Yes"/>
                    <small><?php echo $this->t('{login:remember_username}'); ?></small>
                </td>
            <?php
            }
            ?>
            </tr>
            <?php
            if ($this->data['rememberUsernameEnabled'] && !$this->data['forceUsername']) {
                // display the "remember my username" checkbox
                ?>
            <tr id="mobile_remember_username">
                <td>&nbsp;</td>
                <td>
                    <input type="checkbox" id="remember_username" tabindex="4"
                        <?php echo ($this->data['rememberUsernameChecked']) ? 'checked="checked"' : ''; ?>
                           name="remember_username" value="Yes"/>
                    <small><?php echo $this->t('{login:remember_username}'); ?></small>
                </td>
            </tr>
                <?php
            }
            ?>
            <tr>
                <td><label for="password"><?php echo $this->t('{login:password}'); ?></label></td>
                <td>
                    <input id="password" type="password" tabindex="2" name="password"/>
                </td>
            <?php
            if ($this->data['rememberMeEnabled']) {
                // display the remember me checkbox (keep me logged in)
            ?>
                <td id="regular_remember_me">
                    <input type="checkbox" id="remember_me" tabindex="5"
                        <?php echo ($this->data['rememberMeChecked']) ? 'checked="checked"' : ''; ?>
                           name="remember_me" value="Yes"/>
                    <small><?php echo $this->t('{login:remember_me}'); ?></small>
                </td>
            <?php
            }
            ?>
            </tr>
            <?php
            if ($this->data['rememberMeEnabled']) {
                // display the remember me checkbox (keep me logged in)
            ?>
            <tr>
                <td></td>
                <td id="mobile_remember_me">
                    <input type="checkbox" id="remember_me" tabindex="5"
                        <?php echo ($this->data['rememberMeChecked']) ? 'checked="checked"' : ''; ?>
                           name="remember_me" value="Yes"/>
                    <small><?php echo $this->t('{login:remember_me}'); ?></small>
                </td>
            </tr>
            <?php
            }
            ?>
            <?php
            if (array_key_exists('organizations', $this->data)) {
                ?>
                <tr>
                    <td></td>
                    <td><label for="organization"><?php echo $this->t('{login:organization}'); ?></label></td>
                    <td><select name="organization" tabindex="3">
                            <?php
                            if (array_key_exists('selectedOrg', $this->data)) {
                                $selectedOrg = $this->data['selectedOrg'];
                            } else {
                                $selectedOrg = null;
                            }

                            foreach ($this->data['organizations'] as $orgId => $orgDesc) {
                                if (is_array($orgDesc)) {
                                    $orgDesc = $this->t($orgDesc);
                                }

                                if ($orgId === $selectedOrg) {
                                    $selected = 'selected="selected" ';
                                } else {
                                    $selected = '';
                                }

                                echo '<option '.$selected.'value="'.htmlspecialchars($orgId).'">'.htmlspecialchars($orgDesc).'</option>';
                            }
                            ?>
                        </select></td>
                </tr>
                <?php
            }
            ?>
            <tr id="regularsubmit">
                <td></td><td></td>
                <td>
                    <button class="btn" tabindex="6">
                        <?php echo $this->t('{login:login_button}'); ?>
                    </button>
                </td>
            </tr>
            <tr id="mobilesubmit">
                <td></td>
                <td>
                    <button class="btn" tabindex="6">
                        <?php echo $this->t('{login:login_button}'); ?>
                    </button>
                </td>
                <td></td>
            </tr>
        </table>
        <?php
        foreach ($this->data['stateparams'] as $name => $value) {
            echo('<input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" />');
        }
        ?>
    </form>
<?php
if (!empty($this->data['links'])) {
    echo '<ul class="links" style="margin-top: 2em">';
    foreach ($this->data['links'] as $l) {
        echo '<li><a href="'.htmlspecialchars($l['href']).'">'.htmlspecialchars($this->t($l['text'])).'</a></li>';
    }
    echo '</ul>';
}

$this->includeAtTemplateBase('includes/footer.php');
