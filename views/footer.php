<div id="footer">
 <div class="left">
  <?= makePopupLink( '?view=state', 'zmState', 'state', $status, canEdit( 'System' ) ) ?> <span>-</span> 
  <?= makePopupLink( '?view=version', 'zmVersion', 'version', "v".ZM_VERSION, canEdit( 'System' ) ) ?>
 </div>
 <div class="right">
  <?php
   if ( ZM_OPT_USE_AUTH ){
  ?>
   <?= $SLANG['LoggedInAs'] ?> <?= makePopupLink( '?view=logout', 'zmLogout', 'logout', $user['Username'], (ZM_AUTH_TYPE == "builtin") ) ?>, <?= strtolower( $SLANG['ConfiguredFor'] ) ?>
  <?php
   }else{
  ?>
   <?= $SLANG['ConfiguredFor'] ?>
  <?php }?>
  <?= makePopupLink( '?view=bandwidth', 'zmBandwidth', 'bandwidth', $bwArray[$_COOKIE['zmBandwidth']], ($user && $user['MaxBandwidth'] != 'low' ) ) ?> <?= $SLANG['Bandwidth'] ?>
 </div>
</div>
</body>
</html>

