<div  style="margin:0;padding:0;position:fixed;bottom:0;width:100%;">
	<div id="footer">
		<div class="left">
			<a href="?view=state" class="colorbox" style="color:#036;"><?=$status?></a> <span>-</span> 
			<?= makePopupLink( '?view=version', 'zmVersion', 'version', "v".ZM_VERSION, canEdit( 'System' ) ) ?>
		</div>
		<div class="right">
			<?php   if ( ZM_OPT_USE_AUTH ){ ?>
			<?= $SLANG['LoggedInAs'] ?> <a href="?view=logout" class="colorbox" style="color:#036;" ><?=$user['Username']?></a>, <?= strtolower( $SLANG['ConfiguredFor'] ) ?>
			<?php   }else echo $SLANG['ConfiguredFor'] ?>
			<?= makePopupLink( '?view=bandwidth', 'zmBandwidth', 'bandwidth', $bwArray[$_COOKIE['zmBandwidth']], ($user && $user['MaxBandwidth'] != 'low' ) ) ?> <?= $SLANG['Bandwidth'] ?>
		</div>
	</div>
</div>

</body>
</html>

