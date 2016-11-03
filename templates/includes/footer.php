<?php
if(!empty($this->data['htmlinject']['htmlContentPost'])) {
	foreach($this->data['htmlinject']['htmlContentPost'] AS $c) {
		echo $c;
	}
}
?>
	</div><!-- #content -->
	<div id="footer">
		<hr />

		<img class="atlas-logo" src="/<?php echo $this->data['baseurlpath'] ?>resources/icons/atlassoft7a.png" alt="Atlas Soft Dashboard" />
		Copyright © 2016 <a href="https://atlassoft.hu">Atlas Soft Kft</a>
		
		<br style="clear: right" />
	
	</div><!-- #footer -->

</div><!-- #wrap -->

</body>
</html>
