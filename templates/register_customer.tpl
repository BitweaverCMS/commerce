{strip}

		<input type="hidden" name="inline_registration" value="1"/>
		{legend legend="Enter your user information"}
			{if $notrecognized eq 'y'}
				<input type="hidden" name="login" value="{$reg.login}"/>
				<input type="hidden" name="password" value="{$reg.password}"/>
				<input type="hidden" name="novalidation" value="yes"/>

				<div class="form-group {if $userErrors.validate}error{/if}">
					{formlabel label="Username" for="email"}
					{forminput}
						<input class="form-control"type="text" name="email" id="email" value="{$reg.email|default:$smarty.request.email}"/>
						{formhelp note=$userErrors.validate}
					{/forminput}
				</div>

				<div class="form-group submit">
					<input type="submit" class="btn btn-default" name="register" value="{tr}register{/tr}" />
				</div>
			{elseif $showmsg ne 'y'}

		<p>{tr}Please enter your email and password below. If this is your first time here, an account will be created for you. If you have registered previously, you will automatically be logged in.{/tr}</a></p>
				<div class="form-group {if $userErrors.email}error{/if}">
					{formlabel label="Email" for="email"}
					{forminput}
						<input class="form-control"type="text" name="email" id="email" value="{$reg.email|default:$smarty.request.email}" />
						{formhelp note=$userErrors.email}
					{/forminput}
				</div>

				{if $gBitSystem->isFeatureActive('users_register_passcode')}
					<div class="form-group {if $userErrors.passcode}error{/if}">
						{formlabel label="Passcode to register<br />(not your user password)" for="passcode"}
						{forminput}
							<input class="form-control"type="password" name="passcode" id="passcode" />
							{formhelp note=$userErrors.passcode}
						{/forminput}
					</div>
				{/if}

				{if $gBitSystem->isFeatureActive( 'validateUsers' )}
					<div class="form-group">
						{formfeedback warning="A confirmation email will be sent to you with instructions how to login"}
					</div>
				{else}
					<div class="form-group {if $userErrors.password}error{/if}">
						{formlabel label="Password" for="pass"}
						{forminput}
							<input class="form-control"id="pass1" type="password" name="password" value="{$reg.password|default:$smarty.request.password}" />
							{formhelp note=$userErrors.password}
							{formhelp note="If this is your first time registering, confirm your password below."}
						{/forminput}
					</div>

					<div class="form-group {if $userErrors.password2}error{/if}">
						{formlabel label="Repeat password" for="password2"}
						{forminput}
							<input class="form-control"id="password2" type="password" name="password2" value="{$smarty.request.password2}" />
							{formhelp note=$userErrors.password2}
						{/forminput}
					</div>

					{if $gBitSystem->isFeatureActive( 'user_password_generator' )}
						<div class="form-group">
							{formlabel label="<a href=\"javascript:BitBase.genPass('genepass','pass1','pass2');\">{tr}Generate a password{/tr}</a>" for="email"}
							{forminput}
								<input class="form-control"id="genepass" type="text" />
								{formhelp note="You can use this link to create a random password. Make sure you make a note of it somewhere to log in to this site in the future."}
							{/forminput}
						</div>
					{/if}
				{/if}

				{section name=f loop=$customFields}
					<div class="form-group">
						{formlabel label=$customFields[f]}
						{forminput}
							<input class="form-control"type="text" name="CUSTOM[{$customFields[f]|escape}]" />
						{/forminput}
					</div>
				{/section}

				{captcha errors=$userErrors}

			{/if}
		{/legend}
{/strip}
