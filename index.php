<?php
//POWERED BY WeirdLinks SOURCE-CODE ORIGINAL FROM https://weirdlinks.rf.gd
//BY arthur_sales9


//tudo isso foi vibecodado por sinal kkkkkkk
//pqp eu sou o pior "programador"(ENTRE MUITAS ASPAS) do mundo lol

session_start();

$admin_password = [
	"rosy"
];

$server = "rosylolmao.github.io/weirdlinks-on-github";
$user = "[USER HERE]";
$pass = "[PASS HERE]";
$bank = "[BANK HERE]";

$conn = new mysqli($server,$user,$pass,$bank);

if ($conn->connect_error){
	die("Failed connection: " . $conn->connect_error);
}

$conn->query("CREATE TABLE IF NOT EXISTS posts (
	id INT AUTO_INCREMENT PRIMARY KEY,
	author VARCHAR(30) NOT NULL,
	message TEXT NOT NULL,
	user_color VARCHAR(7) DEFAULT '#00FF66',
	tripcode VARCHAR(12) DEFAULT '',
	creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	reply_to INT DEFAULT 0,
	image_url VARCHAR(500) DEFAULT ''
)");

$check_staff = $conn->query("SHOW COLUMNS FROM posts LIKE 'is_staff'");
if ($check_staff->num_rows == 0) {
	$conn->query("ALTER TABLE posts ADD COLUMN is_staff TINYINT(1) DEFAULT 0");
}

$check_img = $conn->query("SHOW COLUMNS FROM posts LIKE 'image_url'");
if ($check_img->num_rows == 0) {
	$conn->query("ALTER TABLE posts ADD COLUMN image_url VARCHAR(500) DEFAULT ''");
}

if (isset($_POST["admin_login"])) {
	if (in_array($_POST["admin_pass"] ?? "", $admin_password, true)) {
		$_SESSION["is_admin"] = true;
	}
	header("Location: index.php");
	exit();
}

if (isset($_GET["admin_logout"])) {
	session_destroy();
	header("Location: index.php");
	exit();
}

if (isset($_GET["delete_id"]) && ($_SESSION["is_admin"] ?? false) === true) {
	$del_id = intval($_GET["delete_id"]);
	if ($del_id > 0) {
		$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
		$stmt->bind_param("i", $del_id);
		$stmt->execute();
		$stmt->close();
	}
	header("Location: index.php");
	exit();
}

$check_col = $conn->query("SHOW COLUMNS FROM posts LIKE 'reply_to'");
if ($check_col->num_rows == 0) {
	$conn->query("ALTER TABLE posts ADD COLUMN reply_to INT DEFAULT 0");
}

$salt_secret = "[YOUR SECRET RANDOM KEY HERE]";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
	$author = trim($_POST["author"]);
	$message = trim($_POST["message"]);
	$user_color = $_POST["user_color"] ?? "#00FF66";
	$secret = trim($_POST["secret"] ?? "");
	$reply_to   = intval($_POST["reply_to"] ?? 0);
	$image_url  = trim($_POST["image_url"] ?? "");
	$post_as_admin = (($_SESSION["is_admin"] ?? false) === true) ? 1 : 0;
	
	if (!empty($author) && !empty($message)){
		if (!empty($image_url)) {
			if (!filter_var($image_url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $image_url)) {
				$image_url = "";
			}
		}
		
		setcookie("wl_author",$author,time()+(86400 * 30), "/");
		setcookie("wl_color",$user_color,time()+(86400 * 30), "/");
		setcookie("wl_secret",$secret,time()+(86400 * 30), "/");
		setcookie("wl_avatar", $image_url, time()+(86400 * 30), "/");
		
		$tripcode = "";
		if (!empty($secret)){
			$tripcode = "!" . substr(hash("sha256",$secret . $salt_secret),0,8);
		}
		
		if (!preg_match('/^#[a-f0-9]{6}$/i', $user_color)){
			$user_color = "#000080";
		}
		
		if ($reply_to < 0){
			$reply_to = 0;
		}
		
		$stmt = $conn->prepare("INSERT INTO posts (author,message,user_color,tripcode, reply_to,image_url,is_staff) VALUES (?,?,?,?,?,?,?)");
		
		$stmt->bind_param("ssssisi",$author,$message,$user_color,$tripcode,$reply_to,$image_url,$post_as_admin);
		$stmt->execute();
		$stmt->close();
		
		header("Location: index.php");
		exit();
	}
}
$saved_author = htmlspecialchars($_COOKIE["wl_author"] ?? "");
$saved_color  = htmlspecialchars($_COOKIE["wl_color"] ?? "#00FF66");
$saved_secret = htmlspecialchars($_COOKIE["wl_secret"] ?? "");
$saved_avatar = htmlspecialchars($_COOKIE["wl_avatar"] ?? "");

$sql = "SELECT p.*, orig.author AS target_author, orig.message AS target_message
	FROM posts p
	LEFT JOIN posts orig ON p.reply_to = orig.id
	ORDER BY p.id DESC
	LIMIT 50";

$result = $conn->query($sql);
$is_admin = ($_SESSION["is_admin"] ?? false) === true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WeirdLinks</title>
	<link rel="icon" type="image/png" href="favicon.png">
</head>
<body bgcolor="#000000" text="FFFFFF">
	<?php if ($is_admin): ?>
		<table border="1" cellpadding="5" width="100%" bgcolor="#4A0000">
			<tr>
				<td>
					<font color="#FF5555" face="monospace"><b>[!] ADMIN MODE ACTIVE</b></font> -
					<a href="index.php?admin_logout=1"><font color="#FFFFFF">[Logout Admin]</font></a>
				</td>
			</tr>
		</table><br>
	<?php endif; ?>
	
	<table border="0" cellpadding="10" width="100%">
		<tr>
			<td>
				<img src="logo.png" alt="WeirdLinks">
				<p>Definetively a WEIRD site for WEIRD people.</p>
			</td>
		<td valign="top">
			<table border="1" cellpadding="10" bgcolor="#010101" width="420">
				<tr>
					<td>
						<font color="#CC00FF" face="monospace" size="4"><b> WeirdLink Rules:</b></font><br><br>
						<b> Do not post hate speech/harassment, do not post explicit pornography, do not post illegal shit and gore, and do not spam/flood the feed. If you break the rules, you're not a weirdo, just a dweeb who is not welcome here.</b>
					</td>
				</tr>
			</table>
		</td>
		</tr>
	</table>
	<hr>
	
	<form method="POST" action="index.php">
		<b>Your username:</b><br>
		<input type="text" name="author" maxlength="30" required value="<?php echo $saved_author; ?>" placeholder="Ex: EdgyKid69"><br>
		
		<b>Name Color:</b><br>
		<input type="color" name="user_color" value="<?php echo $saved_color; ?>"><br>
		&nbsp;&nbsp;
		
		<b>Secret Tripcode (Optional):</b><br>
		<input type="password" name="secret" maxlength="30" value="<?php echo $saved_secret; ?>" placeholder="Anti-impostor pass"><br>
		<small><i>*Your profile is saved automatically in your browser. Use a secret pass to generate a unique ID!</i></small><br><br>
		
		<b>Profile Image/Reaction Image</b><br>
		<input type="url" name="image_url" maxlength="500" value="<?php echo $saved_avatar; ?>" placeholder="https://example.com" style="width: 280px;"><br>
		<small><i>*Direct link to PNG, JPG or GIF (64x64px avatar per post).</i></small><br><br>
		
		<b>Mention Post ID (Optional):</b><br>
		<input type="number" name="reply_to" min="1" placeholder="Ex: 1" style="width: 100px;">
		<small><i>*Type a Post No. (#) here to mention/reply to it!</i></small><br><br>
		
		<b>What are you thinking about?</b><br>
		<textarea name="message" rows="4" cols="40" required placeholder="Type here your Link(That's how we call a post here)!"></textarea><br><br>
		
		<button type= "submit">Link it!</button>
	</form>
	
	<hr>
	<h2>
	Posts
	<a href="index.php"><button type="button">[ Refresh Feed ]</button></a>
	</h2>
	
	<?php
	
	if ($result->num_rows > 0){
		while($post = $result->fetch_assoc()){
			$post_id = intval($post["id"]);
			$secure_author = htmlspecialchars($post["author"]);
			$secure_message = htmlspecialchars($post["message"]);
			$secure_color = htmlspecialchars($post["user_color"] ?? "#000080");
			$secure_tripcode = htmlspecialchars($post["tripcode"] ?? "");
			$date = $post["creation_date"];
			$reply_to = intval($post["reply_to"] ?? 0);
			$secure_avatar   = htmlspecialchars($post["image_url"] ?? "");
			$is_post_staff = intval($post["is_staff"] ?? 0);
			
			$formated_message = nl2br($secure_message);
			
			echo "<table id='post-" . $post_id . "' border='1' cellpadding='8' width='400' bgcolor='#202020'>";
			echo "	<tr>";
			if (!empty($secure_avatar)) {
				echo "	<td valign='top' width='68' align='center'>";
				echo "		<a href='" . $secure_avatar . "' target='_blank' title='Click to view full avatar'>";
				echo "			<img src='" . $secure_avatar . "' width='64' height='64' style='object-fit: cover; border: 1px solid #9900FF;' onerror=\"this.style.display='none'\">";
				echo "		</a>";
				echo "	</td>";
			}
			
			echo "		<td>";
			echo "			<b style=color:" . $secure_color . ";'>@" . $secure_author . "</b> <small>(" . $date . ")</small> <font face='monospace' color='#008000'><b>[No. #" . $post_id . "]</b></font><br>";
			
			if ($is_post_staff === 1) {
				echo " <font color='#FF3333'><small><i>[ ! Admin/Staff ! ]</i></small></font> ";
			}else {
				echo " <font color='#888888'><small><i>[ ! User/Guest ! ]</i></small></font> ";
			}
			
			if ($reply_to > 0){
				echo "<table border='1' cellpadding='4' width='100%' bgcolor='#B000E0' style='margin-top: 6px; margin-bottom: 6px;'>";
				echo "	<tr><td>";
				if (!empty($post["target_author"])) {
					$t_author  = htmlspecialchars($post["target_author"]);
					$t_preview = htmlspecialchars(mb_strimwidth($post["target_message"], 0, 50, "..."));
					echo "			<small><b>&gt;&gt; Mentioning <a href='#post-" . $reply_to . "'>No. #" . $reply_to . "</a> (@" . $t_author . "):</b> <i>\"" . $t_preview . "\"</i></small>";
				}else {
					echo "			<small><b>&gt;&gt; Mentioning No. #" . $reply_to . "</b> <i>(Post deleted or doesn't exist)</i></small>";
				}
				echo "	</tr></td>";
				echo "</table>";
			}
			
			
			if (!empty($secure_tripcode)) {
				echo "  <font color='#E4A0A0' face='monospace'><b>" . $secure_tripcode . "</b></font> ";
			}
			
			
			echo "			" . $formated_message;
			if ($is_admin){
				echo "<a href='index.php?delete_id=" . $post_id . "' onclick=\"return confirm('Delete Post #" . $post_id . " forever?');\">";
				echo "	<font color='#FF0000' face='monospace'><b>[DELETE]</b></font>";
				echo "</a>";
			}
			echo "		</td>";
			echo "	</tr>";
			echo "</table><br>";
			
		}
	} else {
		echo "<p><i>No one linked something yet... be the first to share your weird thoughts!</i></p>";
	}
	
	$conn->close();
	
	?>
	<hr>
	<details>
		<summary><small><font color="#666666">[Staff / Admin]</font></small></summary>
		<form method="POST" action="index.php" style="margin-top: 5px;">
			<input type="password" name="admin_pass" placeholder="Admin Password" size="15">
			<button type="submit" name="admin_login">Enter</button>
		</form>
	</details>
</body>
</html>
