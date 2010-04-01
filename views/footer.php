<div id="footer">
        <input type="button" value="<?= $SLANG['Refresh'] ?>" onclick="location.reload(true);"/>
        <?= makePopupButton( '?view=monitor', 'zmMonitor0', 'monitor', $SLANG['AddNewMonitor'], (canEdit( 'Monitors' ) && !$user['MonitorIds']) ) ?>
        <?= makePopupButton( '?view=filter&filter[terms][0][attr]=DateTime&filter[terms][0][op]=%3c&filter[terms][0][val]=now', 'zmFilter', 'filter', $SLANG['Filters'], canView( 'Events' ) ) ?>
        <input type="button" name="deleteBtn" value="<?= $SLANG['Delete'] ?>" onclick="deleteMonitor( this )"/>
        <?= makePopupLink( '?view=version', 'zmVersion', 'version', "v".ZM_VERSION, canEdit( 'System' ) ) ?>
</div>
    </div>
</body>
</html>

