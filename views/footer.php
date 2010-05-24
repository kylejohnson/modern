<div id="footer">
      <div class="left"><?= makePopupLink( '?view=groups', 'zmGroups', 'groups', sprintf( $CLANG['MonitorCount'], count($displayMonitors), zmVlang( $VLANG['Monitor'], count($displayMonitors) ) ).($group?' ('.$group['Name'].')':''), canView( 'System' ) ); ?></div>
 <div class="center"><?= makePopupLink( '?view=state', 'zmState', 'state', $status, canEdit( 'System' ) ) ?></div>
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
   <?= makePopupLink( '?view=version', 'zmVersion', 'version', "v".ZM_VERSION, canEdit( 'System' ) ) ?>
 </div>
</div>
    </div>
</body>
</html>

