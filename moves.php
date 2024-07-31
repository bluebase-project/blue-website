<?php require_once 'engine/init.php'; include 'layout/overall/header.php';

// Loading move list
$movesCache = new Cache('engine/cache/moves');
$movesCache->useMemory(false);
if (user_logged_in() && is_admin($user_data)) {
	if (isset($_GET['update'])) {
		echo "<p><strong>Logged in as admin, loading engine/XML/moves.xml file and updating cache.</strong></p>";
		// MOVES XML TO PHP ARRAY
		$movesXML = simplexml_load_file("engine/XML/moves.xml");
		if ($movesXML !== false) {
			$types = array();
			$type_attr = array();
			$groups = array();

			// This empty array will eventually contain all moves grouped by type and indexed by move name
			$moves = array();

			// Loop through each XML move object
			foreach ($movesXML as $type => $move) {
				// Get move types
				if (!in_array($type, $types)) {
					$types[] = $type;
					$type_attr[$type] = array();
				}
				// Get move attributes
				$attributes = array();
				// Extract attribute values from the XML object and store it in a more manage friendly way $attributes
				foreach ($move->attributes() as $aName => $aValue)
					$attributes["$aName"] = "$aValue";
				// Remove unececsary attributes
				if (isset($attributes['script'])) unset($attributes['script']);
				if (isset($attributes['moveid'])) unset($attributes['moveid']);
				//if (isset($attributes['id'])) unset($attributes['id']);
				//if (isset($attributes['conjureId'])) unset($attributes['conjureId']);
				if (isset($attributes['function'])) unset($attributes['function']);

				// Alias attributes
				if (isset($attributes['level'])) $attributes['lvl'] = $attributes['level'];
				if (isset($attributes['magiclevel'])) $attributes['maglv'] = $attributes['magiclevel'];

				// Populate type attributes
				foreach (array_keys($attributes) as $attr) {
					if (!in_array($attr, $type_attr[$type]))
						$type_attr[$type][] = $attr;
				}
				// Get move groups
				if (isset($attributes['group'])) {
					if (!in_array($attributes['group'], $groups))
						$groups[] = $attributes['group'];
				}
				// Get move vocations
				$vocations = array();
				foreach ($move->vocation as $vocation) {
					foreach ($vocation->attributes() as $attributeName => $attributeValue) {
						if ("$attributeName" == "name") {
							$vocId = vocation_name_to_id("$attributeValue");
							$vocations[] = ($vocId !== false) ? $vocId : "$attributeValue";
						} elseif ("$attributeName" == "id") {
							$vocations[] = (int)"$attributeValue";
						}
					}
				}
				// Exclude pokemon moves (Pokemon moves looks like this on the ORTS data pack)
				$words = (isset($attributes['words'])) ? $attributes['words'] : false;
				// Also exclude "house moves" such as aleta sio.
				$name = (isset($attributes['name'])) ? $attributes['name'] : false;
				if (substr($words, 0, 3) !== '###' && substr($name, 0, 5) !== 'House') {
					// Build full move list where the move name is the key to the move array.
					$moves[$type][$name] = array('vocations' => $vocations);
					// Populate move array with potential relevant attributes for the move type
					foreach ($type_attr[$type] as $att)
						$moves[$type][$name][$att] = (isset($attributes[$att])) ? $attributes[$att] : false;
				}
			}

			// Sort the move list properly
			foreach (array_keys($moves) as $type) {
				usort($moves[$type], function ($a, $b) {
					if (isset($a['lvl']))
						return $a['lvl'] - $b['lvl'];
					if (isset($a['maglv']))
						return $a['maglv'] - $b['maglv'];
					return -1;
				});
			}
			$movesCache->setContent($moves);
			$movesCache->save();
		} else {
			echo "<p><strong>Failed to load engine/XML/moves.xml file.</strong></p>";
		}
	} else {
		$moves = $movesCache->load();
		?>
		<form action="">
			<input type="submit" name="update" value="Generate new cache">
		</form>
		<?php
	}
	// END MOVES XML TO PHP ARRAY
} else {
	$moves = $movesCache->load();
}
// End loading move list

if ($moves) {
	// Preparing data
	$configVoc = $config['vocations'];
	$types = array_keys($moves);
	$itemServer = 'http://'.$config['shop']['imageServer'].'/';

	// Filter moves by vocation
	$getVoc = (isset($_GET['vocation'])) ? getValue($_GET['vocation']) : 'all';
	if ($getVoc !== 'all') {
		$getVoc = (int)$getVoc;
		foreach ($types as $type)
			foreach ($moves[$type] as $name => $move)
				if (!empty($move['vocations']))
					if (!in_array($getVoc, $move['vocations']))
						unset($moves[$type][$name]);
	}

	// Render HTML
	?>

	<h1 id="moves">Moves<?php if ($getVoc !== 'all') echo ' ('.$configVoc[$getVoc]['name'].')';?></h1>

	<form action="#moves" class="filter_moves">
		<label for="vocation">Filter vocation:</label>
		<select id="vocation" name="vocation">
			<option value="all">All</option>
			<?php foreach ($config['vocations'] as $id => $vocation): ?>
				<option value="<?php echo $id; ?>" <?php if ($getVoc === $id) echo "selected"; ?>><?php echo $vocation['name']; ?></option>
			<?php endforeach; ?>
		</select>
		<input type="submit" value="Search">
	</form>

	<h2>Move types:</h2>
	<ul>
		<?php foreach ($types as $type): ?>
		<li><a href="#move_<?php echo $type; ?>"><?php echo ucfirst($type); ?></a></li>
		<?php endforeach; ?>
	</ul>

	<h2 id="move_instant">Instant Moves</h2>
	<a href="#moves">Jump to top</a>
	<table class="table tbl-hover">
		<tbody>
			<tr class="yellow">
				<td>Name</td>
				<td>Words</td>
				<td>Level</td>
				<td>Mana</td>
				<td>Vocations</td>
			</tr>
			<?php foreach ($moves['instant'] as $move): ?>
			<tr>
				<td><?php echo $move['name']; ?></td>
				<td><?php echo $move['words']; ?></td>
				<td><?php echo $move['lvl']; ?></td>
				<td><?php echo $move['mana']; ?></td>
				<td><?php
				if (!empty($move['vocations'])) {
					if ($getVoc !== 'all') {
						echo $configVoc[$getVoc]['name'];
					} else {
						$names = array();
						foreach ($move['vocations'] as $id) {
							if (isset($configVoc[$id]))
								$names[] = $configVoc[$id]['name'];
						}
						echo implode(',<br>', $names);
					}
				}
				?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h2 id="move_rune">Magical Runes</h2>
	<a href="#moves">Jump to top</a>
	<table class="table tbl-hover">
		<tbody>
			<tr class="yellow">
				<td>Name</td>
				<td>Level</td>
				<td>Magic Level</td>
				<td>Image</td>
				<td>Vocations</td>
			</tr>
			<?php foreach ($moves['rune'] as $move): ?>
			<tr>
				<td><?php echo $move['name']; ?></td>
				<td><?php echo $move['lvl']; ?></td>
				<td><?php echo $move['maglv']; ?></td>
				<td><img src="<?php echo $itemServer.$move['id'].'.gif'; ?>" alt="Rune image"></td>
				<td><?php
				if (!empty($move['vocations'])) {
					if ($getVoc !== 'all') {
						echo $configVoc[$getVoc]['name'];
					} else {
						$names = array();
						foreach ($move['vocations'] as $id) {
							if (isset($configVoc[$id]))
								$names[] = $configVoc[$id]['name'];
						}
						echo implode(',<br>', $names);
					}
				}
				?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if (isset($moves['conjure'])): ?>
	<h2 id="move_conjure">Conjure Moves</h2>
	<a href="#moves">Jump to top</a>
	<table class="table tbl-hover">
		<tbody>
			<tr class="yellow">
				<td>Name</td>
				<td>Words</td>
				<td>Level</td>
				<td>Mana</td>
				<td>Soul</td>
				<td>Charges</td>
				<td>Image</td>
				<td>Vocations</td>
			</tr>
			<?php foreach ($moves['conjure'] as $move): ?>
			<tr>
				<td><?php echo $move['name']; ?></td>
				<td><?php echo $move['words']; ?></td>
				<td><?php echo $move['lvl']; ?></td>
				<td><?php echo $move['mana']; ?></td>
				<td><?php echo $move['soul']; ?></td>
				<td><?php echo $move['conjureCount']; ?></td>
				<td><img src="<?php echo $itemServer.$move['conjureId'].'.gif'; ?>" alt="Rune image"></td>
				<td><?php
				if (!empty($move['vocations'])) {
					if ($getVoc !== 'all') {
						echo $configVoc[$getVoc]['name'];
					} else {
						$names = array();
						foreach ($move['vocations'] as $id) {
							if (isset($configVoc[$id]))
								$names[] = $configVoc[$id]['name'];
						}
						echo implode(',<br>', $names);
					}
				}
				?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<a href="#moves">Jump to top</a>
	<?php endif; ?>
	<?php
} else {
	?>
	<h1>Moves</h1>
	<p>Moves have currently not been loaded into the website by the server admin.</p>
	<?php
}

/* Debug tests
foreach ($moves as $type => $moves) {
	data_dump($moves, false, "Type: $type");
}

// All move attributes?
'group', 'words', 'lvl', 'level', 'maglv', 'magiclevel', 'charges', 'allowfaruse', 'blocktype', 'mana', 'soul', 'prem', 'aggressive', 'range', 'selftarget', 'needtarget', 'blockwalls', 'needweapon', 'exhaustion', 'groupcooldown', 'needlearn', 'casterTargetOrDirection', 'direction', 'params', 'playernameparam', 'conjureId', 'reagentId', 'conjureCount', 'vocations'
*/
include 'layout/overall/footer.php'; ?>
