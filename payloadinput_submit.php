<?php

require 'payload.php';

function checkInput($keys) {
	foreach ($keys as $key) {
		if (empty($_POST[$key])) {
			die("" . ucfirst($key) . " must be set for config to be valid!");
		}
	}
}

checkInput(['description', 'email', 'name', 'username']);
$description = $_POST["description"];
$email = $_POST["email"];
$fullname = $_POST["name"];
$username = $_POST["username"];
$org = 'SomeOrg';
$file_name = $username . '_profile.mobileconfig';
$server_in = "some.server.com";
$server_out = $server_in;
$file_name = $username . '_mail.mobileconfig';

// Add headers to set content type to xml and download on submit

header("Content-Type: application/xml; charset=utf-8");
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: Attachment; filename=\"" . $file_name . "\"");

$payload = new Payload($org);
$identifier = $payload->getIdentifier();
$mail = new Mail($description, $fullname, $email, $username, $identifier, $server_in, $server_out, $org);
$payload->addPayloadContent($mail->getXML());
$xml = $payload->getXML()
?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
<?=$xml?>
</dict>
</plist>
