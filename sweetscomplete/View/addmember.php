<?php
// assign defaults
$mailStatus = '';
// user input data
$data = array('email' 		=> '',
			  'firstname' 	=> '',
			  'lastname' 	=> '',
			  'address' 	=> '',
			  'postal_code' 	=> '',
			  'city' 		=> '',
			  'state_province' 	=> '',
			  'country'		=> '',
			  'dob'			=> '', 	// can be built from next 3 fields
			  'dobyear'		=> 0,
			  'dobmonth'	=> 0,
			  'dobday'		=> 0,
			  'phone' 	=> '',
			  'password' 	=> '',
			  'photo' 		=> '',
);
// validation errors
$error = array('email' 	  => '',
			  'firstname' => '',
			  'lastname'  => '',
			  'address'	  => '',
			  'city'	  => '',
			  'state_province' => '',
			  'country'	  => '',
			  'postal_code'  => '',
			  'phone' => '',
			  'dob'		  => '',
			  'password'  => '',
			  'photo'	  => '',
			  'captcha'	  => '',
			  'other'	  => '',
);
// moved this code outside of "if"
require_once __DIR__ . '/../Model/Members.php';
$member = new Members();

// implement country code lookup validation
require_once __DIR__ . '/../Model/Iso_Codes.php';
$codes = new Iso_Codes();

// build country code validation array + HTML select
$selectedCountry = (isset($_POST['data']['country'])) ? $_POST['data']['country'] : 'UK';
$countrySelect = '<select name="data[country]">' . PHP_EOL;
$countryValidate = array();
foreach($codes->getIso2AndNames() as $iso2 => $name) {
	$countryValidate[] = $iso2;
	$countrySelect .= '<option value="' . $iso2 . '"';
	$countrySelect .= ($selectedCountry == $iso2) ? ' selected ' : '';
	$countrySelect  .= '>' . $name . '</option>' . PHP_EOL;
}
$countrySelect .= '</select>' . PHP_EOL;

// validate $_POST data
if (isset($_POST['data'])) {
	
	$data = $_POST['data'];
	
	// set validate flag
	$valid = TRUE;
	
	// captcha
	$phrase	= (isset($_POST['data']['phrase']))   ? $_POST['data']['phrase'] 	: '';
	$phrase = strip_tags(trim($phrase));
	if (!(isset($_SESSION['phrase']) && $_SESSION['phrase'] == $phrase)) {
		$valid = FALSE;
		$error['captcha'] = 'Please re-enter CAPTCHA';
	}

	if (isset($data['dobyear']) && isset($data['dobmonth']) && isset($data['dobday'])) {
		try {
			// NOTE: sprintf %xd will change the data type to int
			$bdateString = sprintf('%4d-%02d-%02d', $data['dobyear'], $data['dobmonth'], $data['dobday']);
			$bdate 		 = new DateTime($bdateString);
			$today 		 = new DateTime();
			$interval21  = new DateInterval('P21Y');
			$bdate21 	 = $today->sub($interval21);
			// *** validation: need to check to see if DOB is > 21 years old
			if ($bdate > $bdate21) {
				$error['dob'] = 'Must be at least 21 years of age!';
				$valid = FALSE;
			}
			// NOTE: final dob never directly uses user-supplied data!
			$data['dob'] = $bdate->format('Y-m-d');
		} catch (Exception $e) {
			// *** security: log the error message rather than displaying it
			error_log($e->getMessage(), 0);
			header('Location: ?page=home');
			exit;
		}

	} else {
		// *** validation: need to add info to $error['dob']
		$error['dob'] = 'Please be sure to enter a year, month and day!';
		$valid = FALSE;
	}
	// filter out any tags and extra white space
		foreach ($data as $key => $value) {
		$data[$key] = strip_tags(trim($value));
	}
	/*
	 * NOTE: preg_match() could be used for any validation
	 * Example:
	if (!preg_match('/^[a-z][a-z0-9._-]+@(\w+\.)+[a-z]{2,6}$/i', $data['email'])) {
		$error['email'] = '<b class="error">Invalid email address</b>';
	}
	 */
	// email
	if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
		$error['email'] = 'Please enter a valid email address';
		$valid = FALSE;
	}
	// first and last name
	// NOTE: length checking done using strlen() using database column sizes as a guide
	if (!preg_match('/^[a-z,. ]+$/i', $data['firstname'])) {
		$error['firstname'] = 'Only letters and punctuation allowed';
		$valid = FALSE;
	} elseif (strlen($data['firstname']) > 128) {
		$error['firstname'] = 'First name can only be 128 letters long';
		$valid = FALSE;
	}
	if (!preg_match('/^[a-z,. ]+$/i', $data['lastname'])) {
		$error['lastname'] = 'Only letters and punctuation allowed';
		$valid = FALSE;
	} elseif (strlen($data['lastname']) > 128) {
		$error['lastname'] = 'Last name can only be 128 letters long';
		$valid = FALSE;
	}
	// photo: only allow jpg, png or gif
	if (!preg_match('/.*\.(jpg|png|gif)$/i', $data['photo'])) {
		$error['photo'] = 'Photo must be either jpg, png or gif';
		$valid = FALSE;
	} elseif (strlen($data['photo']) > 128) {
		$error['photo'] = 'Photo URL can only be 128 characters long';
		$valid = FALSE;
	}
	// address, city, state/province
	// NOTE: strip out allowed characters using str_replace() and then run ctype_alnum()
	//       this is a faster alternative to preg_match()
	if (!ctype_alnum(str_replace(array(' ',',','.'), '', $data['address']))) {
		$error['address'] = 'Only letters, numbers, commas, periods and spaces allowed';
		$valid = FALSE;
	} elseif (strlen($data['address']) > 255) {
		$error['address'] = 'Address can only be 255 characters long';
		$valid = FALSE;
	}
	if (!ctype_alnum(str_replace(array(' ',',','.'), '', $data['city']))) {
		$error['city'] = 'Only letters, numbers, commas, periods and spaces allowed';
		$valid = FALSE;
	} elseif (strlen($data['city']) > 64) {
		$error['city'] = 'City can only be 64 letters long';
		$valid = FALSE;
	}
	if (!ctype_alnum(str_replace(array(' ',',','.'), '', $data['state_province']))) {
		$error['state_province'] = 'Only letters, numbers, commas, periods and spaces allowed';
		$valid = FALSE;
	} elseif (strlen($data['state_province']) > 32) {
		$error['state_province'] = 'State/province can only be 32 letters long';
		$valid = FALSE;
	}
	// country is checked against the array created above
	if (isset($data['country'])) {
		$countryKey = array_search($data['country'], $countryValidate, TRUE);
		if ($countryKey === FALSE) {
			$error['country'] = 'Country not found on planet Earth!';
			$valid = FALSE;
		} else {
			// NOTE: use only known good data if possible
			$data['country'] = $countryValidate[$countryKey];
		}
	}
	// postal_code: uppercase, spaces or dashes allowed
	$data['postal_code'] = strtoupper($data['postal_code']);
	if (!ctype_alnum(str_replace(array(' ','-'), '', $data['postal_code']))) {
		$error['postal_code'] = 'Only letters, numbers, spaces and dashes allowed';
		$valid = FALSE;
	} elseif (strlen($data['postal_code']) > 10) {
		$error['postal_code'] = 'Postal code can only be 10 characters long';
		$valid = FALSE;
	}
	// phone: only numbers, spaces or dashes allowed
	if (!ctype_digit(str_replace(array(' ','-'), '', $data['phone']))) {
		$error['phone'] = 'Only numbers, spaces and dashes allowed';
		$valid = FALSE;
	} elseif (strlen($data['phone']) > 16) {
		$error['phone'] = 'Phone number can only be 16 characters long';
		$valid = FALSE;
	}
	// NOTE: only validation on password is to make sure there is one
	if (!(isset($data['password']) && $data['password'])) {
		$error['password'] = 'You must enter a password!';
		$valid = FALSE;
	}
	// NOTE: only add data to database if form data is valid
	if ($valid) {
		// add data and retrieve last insert ID
		try {
			list($newId, $confirmCode) = $member->add($data);
			if ($newId) {
				// send email confirmation
				$error['other'] = $member->confirm($newId, $data, $confirmCode);
			} else {
				$error['other'] = 'Unable to save member information';
			}
		} catch (Exception $e) {
			$error['other'] = 'Unable to save member information';
			error_log($e->getMessage, 0);
		}
	}
}

// generate CAPTCHA
require 'captcha.php';
$captcha = new MakeCaptcha();
$captcha->purgeCaptchaFiles();
// record any errors
if ($captcha->generateCaptcha()) {
	$_SESSION['phrase'] = $captcha->captchaPhrase;
} else {
	$error[] = $captcha->error;
}

// generate MD5 hash
$newHash = md5(date('YmdHis') . session_id());
$_SESSION['hash'] = $newHash;

?>
	<div class="content">
	<br/>
	<div class="product-list">

		<h2>Sign Up</h2>
		<br/>

		<b>Please enter your information.</b>
		<br/>
		<br/>
		<?php if ($mailStatus) echo '<br /><b class="confirm">', $mailStatus, '</b><br />'; ?>
		<br />
		<form action="?page=addmember" method="POST">
			<p>
				<!-- // *** validation: birthdate validation is already done -->
				<label>Birthdate: </label>
				<select name="data[dobyear]">
					<?php if ($data['dobyear']) { echo '<option>', (int) $data['dobyear'], '</option>'; } ?>
					<?php $year = date('Y'); ?>
					<?php for($x = $year; $x > ($year - 120); $x--) { ?>
						<option><?php echo $x; ?></option>
					<?php }		?>
				</select>
				<select name="data[dobmonth]">
					<?php
					$month = array(1 => 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
					if ($data['dobmonth']) {
						printf('<option value="%02d">%s</option>',
							   $data['dobmonth'], $month[(int) $data['dobmonth']]);
					}
					for($x = 1; $x <= 12; $x++) {
						printf('<option value="%02d">%s</option>', $x, $month[$x]);
						echo PHP_EOL;
					}
					?>
				</select>
				<select name="data[dobday]">
					<?php if ($data['dobday']) { echo '<option>', (int) $data['dobday'], '</option>'; } ?>
					<?php for($x = 1; $x < 32; $x++) { ?>
						<option><?php echo $x; ?></option>
					<?php }		?>
				</select>
				<?php if ($error['dob']) echo '<b style="color:red;">', $error['dob'], '</b>'; ?>
			<p>
				<label>Email: </label>
				<!-- // all re-displayed values use output escaping -->
				<!-- // Example:  echo htmlspecialchars($data['email']); -->
				<input type="email" name="data[email]" value="<?php echo htmlspecialchars($data['email']); ?>" placeholder="email" />
				<?php if ($error['email']) echo '<b style="color:red;">', $error['email'], '</b>'; ?>
			<p>
			<p>
				<label>First Name: </label>
				<input type="text" name="data[firstname]" value="<?php echo htmlspecialchars($data['firstname']); ?>" placeholder="first name" />
				<?php if ($error['firstname']) echo '<b style="color:red;">', $error['firstname'], '</b>'; ?>
			<p>
			<p>
				<label>Last Name: </label>
				<input type="text" name="data[lastname]" value="<?php echo htmlspecialchars($data['lastname']); ?>" placeholder="last name" />
				<?php if ($error['lastname']) echo '<b style="color:red;">', $error['lastname'], '</b>'; ?>
			<p>
			<p>
				<label>Photo: </label>
				<input type="text" name="data[photo]" value="<?php echo htmlspecialchars($data['photo']); ?>" placeholder="URL of photo" />
				<?php if ($error['photo']) echo '<b style="color:red;">', $error['photo'], '</b>'; ?>
			<p>
			<p>
				<label>Address: </label>
				<input type="text" name="data[address]" value="<?php echo htmlspecialchars($data['address']); ?>" placeholder="address" />
				<?php if ($error['address']) echo '<b style="color:red;">', $error['address'], '</b>'; ?>
			<p>
			<p>
				<label>City: </label>
				<input type="text" name="data[city]" value="<?php echo htmlspecialchars($data['city']); ?>" placeholder="city" />
				<?php if ($error['city']) echo '<b style="color:red;">', $error['city'], '</b>'; ?>
			<p>
			<p>
				<label>State/Province: </label>
				<input type="text" name="data[state_province]" value="<?php echo htmlspecialchars($data['state_province']); ?>" placeholder="state/province" />
				<?php if ($error['state_province']) echo '<b style="color:red;">', $error['state_province'], '</b>'; ?>
			<p>
			<p>
				<label>Country: </label>
				<?php echo $countrySelect; ?>
				<?php if ($error['country']) echo '<b style="color:red;">', $error['country'], '</b>'; ?>
			<p>
			<p>
				<label>postal_code: </label>
				<input type="text" name="data[postal_code]" value="<?php echo htmlspecialchars($data['postal_code']); ?>" placeholder="post code" />
				<?php if ($error['postal_code']) echo '<b style="color:red;">', $error['postal_code'], '</b>'; ?>
			<p>
			<p>
				<label>Telephone: </label>
				<input type="text" name="data[phone]" value="<?php echo htmlspecialchars($data['phone']); ?>" placeholder="phone" />
				<?php if ($error['phone']) echo '<b style="color:red;">', $error['phone'], '</b>'; ?>
			<p>
			<p>
				<label>Password: </label>
				<input type="password" name="data[password]" value="<?php echo htmlspecialchars($data['password']); ?>" />
				<?php if ($error['password']) echo '<b style="color:red;">', $error['password'], '</b>'; ?>
			<p>
			<!-- // CAPTCHA -->
			<p>
				<label>Enter CAPTCHA Text: </label>
				<input type="text" name="data[phrase]" />
				<?php if ($error['captcha']) echo '<b style="color:red;">', $error['captcha'], '</b>'; ?>
			<p>
			<p>
				<label>&nbsp;</label>
				<img src="<?php echo $captcha->captchaURL; ?>" />
			<p>
			<p>
				<input type="reset" name="data[clear]" value="Clear" class="button"/>
				<input type="submit" name="data[submit]" value="Submit" class="button marL10"/>
			<p>
			<?php if ($error['other']) echo '<p><b style="color:red;">', $error['other'], '</b><p>'; ?>
		</form>
	</div><!-- product-list -->
</div>
