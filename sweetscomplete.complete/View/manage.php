<?php
// View/manage.php
if (isset($acl) && $acl->hasRightsToFile(__FILE__)) {
	// do nothing: all OK
} else {
	header('Location: /');
	exit;
}

// get Members table
require __DIR__ . '/../Model/Members.php';
$memberTable = new Members();

// do search
if (isset($_GET['keyword'])) {
	// *** sql injection: safely quote the value for inclusion in an SQL statement
	$search = $_GET['keyword'];
	$members = $memberTable->getMembersByKeyword($search);
} else {
	// figure out how many members
	$howMany = $memberTable->getHowManyMembers();
	// get current offset
	if (isset($_GET['offset'])) {
		$offset = (int) $_GET['offset'];
	} else {
		$offset = 0;
	}
	// figure out if previous or next
	if (isset($_GET['more'])) {
		if ($_GET['more'] == 'next') {
			$offset += $memberTable->membersPerPage;
		} else {
			$offset -= $memberTable->membersPerPage;
		}
	} else {
		$offset = 0;
	}
	// adjust offset if < 0 or > $howMany
	if ($offset < 0) {
		$offset = $howMany - $memberTable->membersPerPage;
	} elseif ($offset > $howMany) {
		$offset = 0;
	}
	$members = $memberTable->getAllMembers($offset);
}

?>
<div class="content">

<br/>
<div class="product-list">
	<h2>Our Members</h2>
	<br/>
		<form name="search" method="get" action="?page=manage" id="search">
			<input type="text" placeholder="keywords" name="keyword" class="s0" />
			<input type="submit" name="search" value="Search Members" class="button marL10" />
			<input type="hidden" name="page" value="manage" />
		</form>
	<br/><br/>
	<a class="pages" href="?page=manage&more=previous&offset=<?php echo $offset; ?>">&lt;prev</a>
	&nbsp;|&nbsp;
	<a class="pages" href="?page=manage&more=next&offset=<?php echo $offset; ?>">next&gt;</a>
	<form name="admin" method="post" action="?page=change" id="change">
	<table>
		<tr>
			<th>Member ID</th><th>Name</th><th>City</th><th>Email</th><th>Change</th>
		</tr>
		<?php foreach ($members as $one) { 		?>
		<tr>
		<!-- // *** rewrite using named params instead of offset numbers (i.e. 'user_id' instead of 0, etc.) -->
		<!-- // *** 0 = user_id; 1 = photo; 2 = city; 3 = email -->
			<?php $id = sprintf('%20.0f', $one['user_id']); ?>
			<td><?php echo $id; ?></td>
			<td>
				<?php if (isset($one['photo'])) : ?>
					<img src="<?php echo $one['photo']; ?>" width="10%" height="10%" />
				<?php else : ?>
					<img src="images/m.gif" />
				<?php endif; ?>
				<?php echo $one['name']; ?>
			</td>
			<td><?php echo $one['city']; ?></td>
			<td><img src="images/e.gif" /> <?php echo $one['email']; ?></td>
			<td>
				<input type="radio" name="change[<?php echo $id; ?>]" value="ok" checked /> OK
				<br />
				<input type="radio" name="change[<?php echo $id; ?>]" value="del" /> Del
				<br />
				<input type="radio" name="change[<?php echo $id; ?>]" value="edit" /> Edit
				<br />
				<input type="radio" name="change[<?php echo $id; ?>]" value="history" /> History
			</td>
		</tr>
		<?php } 								?>
	</table>
	<br />
	<input type="submit" name="admin" value="Edit Members" class="button marL10" />
	</form>
	<br/>

</div>
<br class="clear-all"/>
</div><!-- content -->