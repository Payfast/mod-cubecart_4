<?php
if( !defined( 'CC_INI_SET' ) ) die( "Access Denied" );
/**
 * index.inc.php
 *
 * @copyright Copyright 2008, PayFast (Pty) Ltd
 * @copyright Portions Copyright Devellion Limited 2006
 * @author Jonathan Smit
 */

// {{{ Standard CubeCart
permission( "settings", "read", $halt=TRUE );

require( $glob['adminFolder'] . CC_DS .'includes'. CC_DS .'header.inc.php' );

if( isset( $_POST['module'] ) )
{
	require CC_ROOT_DIR . CC_DS .'modules'. CC_DS .'status.inc.php';
	$cache = new cache( "config.". $moduleName );
	$cache->clearCache();
	//$module = fetchDbConfig($moduleName); // Uncomment this is you wish to merge old config with new
	$module = array(); // Comment this out if you don't want the old config to merge with new
	$msg = writeDbConf( $_POST['module'], $moduleName, $module );
}

$module = fetchDbConfig( $moduleName );
// }}}

// {{{ PayFast
$formActionUrl = $glob['adminFile'] .'?_g='. $_GET['_g'] .'&amp;module='. $_GET['module'];
$methodFsRoot = 'modules/'. $moduleType .'/'. $moduleName;

$default = array(
    'desc' => 'PayFast',
    'merchant_id' => '',
    'merchant_key' => '',
    'server' => 0,
    'default' => 1,
    'status' => 0,
    'debug_log' => 0,
    'debug_email' => '',
    );

$data = array();
foreach( $default as $key => $val )
    $data[$key] = !isset( $module[$key] ) ? $default[$key] : $module[$key];
// }}}
?>

<!-- Initial Paragraph -->
<p>
<a href="http://www.payfast.co.za/" target="_blank">
<img src="<?php echo $methodFsRoot; ?>/admin/logo.gif" width="114" height="31" border="0" alt="PayFast" title="PayFast" /></a>
</p>

<p>
Please <a href="http://www.payfast.co.za/user/register" target="_blank">register</a> on PayFast to use this module.
</p>

<p>
Your <em>Merchant ID</em> and <em>Merchant Key</em> are available on your <a href="http://www.payfast.co.za/acc/integration" target="_blank">Integration page</a> on the PayFast website.
</p>

<!-- Echo message -->
<?php if(isset($msg)) echo msg($msg); ?>

<!-- Form for variables -->
<form action="<?php echo $formActionUrl; ?>" method="post" enctype="multipart/form-data">
<table border="0" cellspacing="1" cellpadding="3" class="mainTable">
<tr>
    <td colspan="2" class="tdTitle">Configuration Settings</td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Status:</strong></td>
    <td class="tdText">
	<select name="module[status]">
		<option value="1" <?php if( $data['status'] == 1 ) echo "selected='selected'"; ?>>Enabled</option>
		<option value="0" <?php if( $data['status'] == 0 ) echo "selected='selected'"; ?>>Disabled</option>
    </select>	</td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Default:</strong></td>
    <td class="tdText">
    	<select name="module[default]">
    		<option value="1" <?php if( $data['default'] == 1 ) echo "selected='selected'"; ?>>Yes</option>
    		<option value="0" <?php if( $data['default'] == 0 ) echo "selected='selected'"; ?>>No</option>
    	</select>
    </td>
</tr>
<tr>
  	<td align="left" class="tdText"><strong>Description:</strong>	</td>
    <td class="tdText"><input type="text" name="module[desc]" value="<?php echo $data['desc']; ?>" class="textbox" size="30" /></td>
</tr>
<tr>
    <td><br /></td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Merchant ID:</strong></td>
    <td class="tdText"><input type="text" name="module[merchant_id]" value="<?php echo $data['merchant_id']; ?>" class="textbox" size="30" /></td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Merchant Key:</strong></td>
    <td class="tdText">
        <input type="text" name="module[merchant_key]" value="<?php echo $data['merchant_key']; ?>" class="textbox" size="30" />
    </td>
</tr>
<tr>
    <td align="left" class="tdText">
        <strong>Server:</strong>
    </td>
    <td class="tdText">
        <select name="module[server]">
            <option value="0" <?php if( $data['server'] == 0 ) echo "selected='selected'"; ?>>Test</option>
            <option value="1" <?php if( $data['server'] == 1 ) echo "selected='selected'"; ?>>Live</option>
        </select>
    </td>
</tr>
<tr>
    <td><br /></td>
</tr>
<tr>
    <td align="left" class="tdText">
        <strong>Debugging:</strong>
    </td>
    <td class="tdText">
        <select name="module[debug_log]">
            <option value="0" <?php if( $data['debug_log'] == 0 ) echo "selected='selected'"; ?>>Off</option>
            <option value="1" <?php if( $data['debug_log'] == 1 ) echo "selected='selected'"; ?>>On</option>
        </select>
    </td>
</tr>
<tr>
    <td align="left" class="tdText">
        <strong>Debug Email:</strong>
    </td>
    <td class="tdText">
        <input type="text" name="module[debug_email]" value="<?php echo $data['debug_email']; ?>" class="textbox" size="30" />
    </td>
</tr>
<tr>
    <td align="right" class="tdText">&nbsp;</td>
    <td class="tdText"><input type="submit" class="submit" value="Edit Config" /></td>
</tr>
</table>
</form>