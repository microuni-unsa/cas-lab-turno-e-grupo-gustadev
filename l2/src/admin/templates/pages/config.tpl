<div class="gradient content-header">
	<img src="../images/icons/silk/database_table.png" class="vam mr5" /><span class="bold text-shadow headline vam">Config</span>
</div>

[{if $configWriteable}]
    <h3 style="border-top:1px solid #ddd; border-bottom:1px solid #ddd; background-color: #eee; padding: 10px;">Admin-Center Credentials</h3>

    <table cellpadding="2" cellspacing="0" width="90%" class="m20" style="table-layout: fixed">
        <colgroup>
            <col style="width:250px;" />
        </colgroup>
        <tr>
            <td>Username</td>
            <td>
                <input type="text" id="username" class="input" name="admin.username" value="[{$config.admin.username}]" />
            </td>
        </tr>
        <tr>
            <td>Password</td>
            <td>
                <input type="password" id="password1" class="input" name="admin.password" onfocus="this.clear();"
                       value="***" />
            </td>
        </tr>
        <tr>
            <td>Verify Password</td>
            <td>
                <input type="password" id="password2" class="input" name="admin.password2" onfocus="this.clear();"
                       value="***" />
            </td>
        </tr>
        <tr>
            <td>Weblicense Token</td>
            <td>
                <input type="text" id="license_token" class="input" name="license_token" value="[{$config.license_token}]" />
            </td>
        </tr>
    </table>
    [{if $is_argon_installed}]
    <h3 style="border-top:1px solid #ddd; border-bottom:1px solid #ddd; background-color: #eee; padding: 10px;">Security configuration</h3>

    <table cellpadding="2" cellspacing="0" width="90%" class="m20" style="table-layout: fixed">
        <colgroup>
            <col style="width:250px;" />
        </colgroup>
        <tr>
            <td>Encryption method</td>
            <td>
                <select name="password_encryption_method" class="input" id="password_encryption_method">
                    <option value="bcrypt" [{if isset($config.security.passwords_encryption_method) && $config.security.passwords_encryption_method == 'bcrypt'}]selected="selected"[{/if}]>bcrypt</option>
                    <option value="argon2i" [{if isset($config.security.passwords_encryption_method) && $config.security.passwords_encryption_method == 'argon2i'}]selected="selected"[{/if}]>argon2i</option>
                </select>
            </td>
        </tr>
    </table>
    [{/if}]
    <h3 style="border-top:1px solid #ddd; border-bottom:1px solid #ddd; background-color: #eee; padding: 10px;">Connection to i-doit System Database</h3>

    <table cellpadding="2" cellspacing="0" width="90%" class="m20" style="table-layout: fixed">
        <colgroup>
            <col style="width:250px;" />
        </colgroup>
        <tr>
            <td>Type</td>
            <td>
                <select name="db_type" class="input" id="db_type">
                    <option value="mysqli" [{if $config.db.host == 'mysqli'}]selected="selected"[{/if}]>MySQLi</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Host</td>
            <td>
                <input type="text" name="db_host" class="input" id="db_host" value="[{$config.db.host}]" />
            </td>
        </tr>
        <tr>
            <td>Port</td>
            <td>
                <input type="text" name="db_port" class="input" id="db_port" value="[{$config.db.port}]" />
            </td>
        </tr>
        <tr>
            <td>Username</td>
            <td>
                <input type="text" name="db_user" class="input" id="db_user" value="[{$config.db.user}]" />
            </td>
        </tr>
        <tr>
            <td>Password</td>
            <td>
                <input type="password" name="db_pass1" class="input" id="db_pass1" onfocus="this.clear();" value="***" />
            </td>
        </tr>
        <tr>
            <td>Verify Password</td>
            <td>
                <input type="password" name="db_pass2" class="input" id="db_pass2" onfocus="this.clear();" value="***" />
            </td>
        </tr>
        <tr>
            <td>Database Name</td>
            <td>
                <input type="text" name="db_name" class="input" id="db_name" value="[{$config.db.name}]" />
            </td>
        </tr>
    </table>
[{else}]
	<div class="warning p10 m10">Attention, your config.inc.php is not writeable. Allow write access for the apache user
		in order to edit this config file from here. (Path: [{$configFilePath}])
	</div>
[{/if}]

<div class="info m10 p10">
	Attention: Changing the i-doit system database connection can result in a non working installation! This change is only needed for <strong>moving i-doit to another database instance</strong>! <br />
	Please double check your changes before saving!
</div>

<div class="m10">
	<div class="fl">
		<button type="button" class="btn bold" id="saveConfig">
			<img src="../images/icons/silk/disk.png" class="mr5" /><span>Save</span>
		</button>
	</div>
	<div class="fl error p5 ml10" id="configSaveResult" style="display:none;"></div>
	<br class="cb" />
</div>

<script type="text/javascript">
	$('saveConfig').on('click', function (ev, e) {

		if ($('password1').value == $('password2').value)
		{
			if ($('db_pass1').value == $('db_pass2').value) {
				if (confirm('Are you sure you want to save?')) {
                    new Ajax.Request('?req=config&action=save',
                        {
                            parameters: {
                                'admin_username': $('username').value,
                                'admin_password': $('password1').value,
                                'db_type':        $('db_type').value,
                                'db_host':        $('db_host').value,
                                'db_port':        $('db_port').value,
                                'db_user':        $('db_user').value,
                                'db_pass':        $('db_pass1').value,
                                'db_name':        $('db_name').value,
                                'license_token':  $('license_token').value,
                                'password_encryption_method': $('password_encryption_method').value
                            },
                            onComplete: function (xhr) {
                                var json = xhr.responseJSON,
                                    message,
                                    $saveResult = $('configSaveResult').removeClassName('note').removeClassName('error');

                                if (json)
                                {
                                    message = json.message;
									$saveResult.addClassName(json.success ? 'note' : 'error');
                                }
                                else
                                {
                                    message = 'Unknown Error. Config was not saved!';
                                    $saveResult.addClassName('error');
                                }

                                $saveResult.update(message).show();
                            }
                        }
                    );
				}
			}
			else alert('Database passwords do not match.')
		}
		else alert('Admin-Center passwords do not match.');

	});
</script>
