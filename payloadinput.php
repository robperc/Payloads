<!DOCTYPE html>
<html>
	<head>
		<title>Mail Config</title>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<style type="text/css">
			body {
				font-family: sans-serif;
				font-size: 10pt;
			}
			div.pref {
				border:1px solid black;
				padding:1em;
				background-color:#ddd;
				margin-top:2em;
			}
			th {
				text-align:left;
				padding-right:1em;
			}
		</style>
	</head>
	<body>
		<!-- Inputs for the keys for the mail profile .config file -->
		<form action='payloadinput_submit.php' method="post">
			<table id='settings'>
				<tr id='description_row'>
					<th>
						<label for="description">Description</label>
					</th>
					<td>
						<input  id="description" name="description" placeholder="John Doe's e-mail" size="40">
					</td>
				</tr>
				<tr id='email_row'>
					<th>
						<label for="username">Email Address</label>
					</th>
					<td>
						<input  id="email" name="email" placeholder="johndoe@any.com" size="40">
					</td>
				</tr>
				<tr id='name_row'>
					<th>
						<label for="name">Name</label>
					</th>
					<td>
						<input  id="name" name="name" placeholder="John Doe" size="40">
					</td>
				</tr>
				<tr id='username_row'>
					<th>
						<label for="username">Username</label>
					</th>
					<td>
						<input  id="username" name="username" placeholder="johndoe" size="40">
					</td>
				</tr>
				<tr id='submit_row'>
					<th> </th>
					<td>
						<input type="submit">
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>