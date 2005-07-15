{strip}

	<div class="header">
		<h1>{tr}Enter your user information{/tr}</h1>
		{if $showmsg eq 'y'}<h2>{$msg}</h2>{/if}
	</div>

		<p>{tr}Please enter your email and password below. If this is your first time here, an account will be created for you. If you have registered previously, you will automatically be logged in.{/tr}</a></p>
		<fieldset>
			{if $notrecognized eq 'y'}
				<input type="hidden" name="login" value="{$reg.login}"/>
				<input type="hidden" name="password" value="{$reg.password}"/>
				<input type="hidden" name="novalidation" value="yes"/>

				<div class="row">
					{formfeedback error=$userErrors.validate}
					{formlabel label="Username" for="email"}
					{forminput}
						<input type="text" name="email" id="email" value="{$reg.email}"/>
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="register" value="{tr}register{/tr}" />
				</div>
			{elseif $showmsg ne 'y'}

				<div class="row">
					{formfeedback error=$userErrors.email}
					{formlabel label="Email" for="email"}
					{forminput}
						<input type="text" name="email" id="email" value="{$reg.email}" /> <acronym title="{tr}Required{/tr}">*</acronym>
					{/forminput}
				</div>

				{if $gBitSystem->mPrefs.useRegisterPasscode eq 'y'}
					<div class="row">
						{formfeedback error=$userErrors.passcode}
						{formlabel label="Passcode to register<br />(not your user password)" for="passcode"}
						{forminput}
							<input type="password" name="passcode" id="passcode" /> <acronym title="{tr}Required{/tr}">*</acronym>
						{/forminput}
					</div>
				{/if}

				{if $gBitSystem->isFeatureActive( 'validateUsers' )}
					<div class="row">
						{formfeedback warning="A confirmation email will be sent to you with instructions how to login"}
					</div>
				{else}
					<div class="row">
						{formfeedback error=$userErrors.password}
						{formlabel label="Password" for="pass"}
						{forminput}
							<input id="pass1" type="password" name="password" value="{$reg.password}" /> <acronym title="{tr}Required{/tr}">*</acronym>
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Repeat password" for="password2"}
						{forminput}
							<input id="password2" type="password" name="password2" /> <acronym title="{tr}Required{/tr}">*</acronym>
						{/forminput}
					</div>

					{if $gBitSystem->isFeatureActive( 'user_password_generator' )}
						<div class="row">
							{formlabel label="<a href=\"javascript:genPass('genepass','pass1','pass2');\">{tr}Generate a password{/tr}</a>" for="email"}
							{forminput}
								<input id="genepass" type="text" />
								{formhelp note="You can use this link to create a random password. Make sure you make a note of it somewhere to log in to this site in the future."}
							{/forminput}
						</div>
					{/if}
				{/if}

				{section name=f loop=$customFields}
					<div class="row">
						{formlabel label="$customFields[f]}
						{forminput}
							<input type="text" name="CUSTOM[{$customFields[f]|escape}]" />
						{/forminput}
					</div>
				{/section}

				{if $gBitSystem->mPrefs.rnd_num_reg eq 'y'}
					<hr />

					<div class="row">
						{formfeedback error=$userErrors.rnd_num_reg}
						{formlabel label="Your registration code"}
						{forminput}
							<img src="{$gBitLoc.USERS_PKG_URL}random_num_img.php" alt="{tr}Random Image{/tr}"/>
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Registration code" for="regcode"}
						{forminput}
							<input type="text" maxlength="8" size="8" name="regcode" id="regcode" /> <acronym title="{tr}Required{/tr}">*</acronym>
							{formhelp note="Please copy the code above into this field. This is a security feature to avoid automatic registration by bots."}
						{/forminput}
					</div>
				{/if}

			{/if}
		</fieldset>

{/strip}
