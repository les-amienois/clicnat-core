<?php
namespace \Picnat\Clicnat;

if (!function_exists('array_column')) {
	/**
	 * @brief Retourne les valeurs d'une colonne d'un tableau d'entrée (en attendant PHP 5.5)
	 */
	function array_column($input, $column_key, $index_key = false) {
		$r = array();
		if ($index_key === false) {
			foreach ($input as $k => $v) {
				$r[] = $v[$column_key];
			}
		} else {
			foreach ($input as $k => $v) {
				$r[$v[$index_key]] = $v[$column_key];
			}
		}
		return $r;
	}
}


function get_db_type_enum($db, $typname) {
	static $types;

	if (!isset($types)) {
		$types = [];
	}

	if (!isset($types[$typname])) {
		$types[$typname] = new clicnat_db_type_enum($db, $typname);
	}
	return isset($types[$typname])?$types[$typname]:false;
}

/**
 * @brief Transformation markdown vers texte (sans les balises md)
 * @param $txt_md le texte en markdown
 * @return texte sans balises markdown
 */
function clicnat_markdown_txt($txt_md) {
	require_once('markdown.php');
	require_once(OBS_DIR.'/Html2Text.php');
	static $html2txt;

	$html = markdown($txt_md);

	if (!isset($html2txt)) {
		$html2txt = new \Html2Text\Html2Text($html, false, array('do_links' => 'none'));
	} else {
		$html2txt->set_html($html);
	}
	return $html2txt->get_text();
}

function csv_clean_string($s,$quote) {
	return str_replace($quote," ",$s);
}

function tmpdir($path="/tmp", $prefix="clicnat") {
	$fn = tempnam($path,$prefix);
	if (!$fn)
		throw new \Exception('peut pas créer de dossier temporaire (tempnam)');
	unlink($fn);
	if (!mkdir($fn))
		throw new \Exception('peut pas créer de dossier temporaire (mkdir)');
	return $fn;
}

/**
 * @brief détermine la quantitée max de mémoire utilisable
 * @return int
 */
function memory_limit() {
	static $s;

	if (isset($s)) return $s;

	$sm = ini_get('memory_limit');
	$unit = strtoupper($sm[strlen($sm)-1]);

	$s = trim($sm, $unit);

	switch ($unit) {
		case 'G':
			$s *= 1024;
		case 'M':
			$s *= 1024;
		case 'K':
			$s *= 1024;
	}

	// on se limitera a 128M si possible
	if ($s>128*1024*1024) {
		$s = 128*1024*1024;
	}

	return $s;
}

/**
 * @brief Obtenir l'instance qui gère le fichier de conf xml
 */
function get_config($fichier = '/etc/baseobs/config.xml') {
	static $c;

	if (!isset($c)) {
		$c = new clicnat_config($fichier);
	}
	return $c;
}

function get_db($init_db=null) {
	static $db;
	if (!is_null($init_db))
		$db = $init_db;
	return $db;
}

/**
 * @brief provide query manager singleton
 */
function bobs_qm() {
	static $qm;

	if (!isset($qm))
		$qm = new bobs_query_manager();

	return $qm;
}

function aonfm_xml($db) {
	return bobs_aonfm::aonfm_xml($db);
}

function aonfm_tri_sys2($a,$b) {
	return bobs_aonfm::aonfm_tri_sys2($a,$b);
}

function aonfm_tri_systematique($a, $b) {
	if ($a['objet']->systematique == $b['objet']->systematique) return 0;
	return ((int)$a['objet']->systematique > (int)$b['objet']->systematique)?1:-1;
}

/**
  * @return bobs_citation
  */
function get_citation($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_citation', 'id_citation');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

function get_classe($db, $id) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_classe', 'classe');
	try {
		return $mngr->get($db, $id);
	} catch (\Exception $e) {
		return null;
	}
}

/**
  * @return bobs_espece
  */
function get_espece($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espece', 'id_espece');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
	    switch ($e->getCode()) {
		case BOBS_ERR_NOTFOUND:
		    throw $e;
		default:
		    return null;
	    }
	}
}

function get_espece_inpn($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espece_inpn', 'id_espece');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
	    switch ($e->getCode()) {
		case BOBS_ERR_NOTFOUND:
		    throw $e;
		default:
		    return null;
	    }
	}
}

function clicnat_cmp_tri_tableau_especes_n_citations($a, $b) {
	if ($a['n_citations'] == $b['n_citations']) return 0;
	return ($a['n_citations'] > $b['n_citations'])? -1: 1;
}

function get_travail($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('clicnat_travaux', 'id_travail');
	try {
		return $mngr->get($db, $id_or_array, clicnat_travaux::instance($db, $id_or_array));
	} catch (\Exception $e) {
		return null;
	}
}

function get_texte($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('clicnat_textes', 'id_texte');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

function get_tag($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_tags', 'id_tag');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

function get_tag_by_ref($db, $ref) {
	static $transltr;

	if (!isset($transltr)) $transltr = array();

	if (!array_key_exists($ref, $transltr))
		$transltr[$ref] = bobs_tags::by_ref($db, $ref);

	return $transltr[$ref];
}

function get_tache($db, $id) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('clicnat_tache', 'id_tache');
	try {
		return $mngr->get($db, $id);
	} catch (\Exception $e) {
		return null;
	}
}

function get_structure($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr)) {
		$mngr = new bobs_single_mngr('clicnat_structure', 'id_structure');
	}
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

//JBH Get structure by name
function get_structure_by_name($db, $ref) {
	if (empty($ref))
		throw new InvalidArgumentException('$ref est vide');
	$q = bobs_qm()->query($db, 'structure_by_name', 'select id_structure from structures where nom=$1', array($ref));
	$r = bobs_element::fetch($q);
  $id_structure = $r['id_structure'];
	$struct = new clicnat_structure($db, $id_structure);

	return $struct;
}

function smarty_modifier_markdown_txt($txt) {
	return clicnat_markdown_txt($txt);
}


/**
  * @return bobs_selection
  */
function get_selection($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_selection', 'id_selection');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @brief CHR : Gestionnaire d'instances
 */
function get_chr($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_chr', bobs_chr::chr_pkey);
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
	    switch ($e->getCode()) {
		case BOBS_ERR_NOTFOUND:
		    throw $e;
		default:
		    return null;
	    }
	}
}


/**
 * @param ressource $db
 * @param int $id_or_array
 * @return bobs_espace_structure objet bobs_espace_point
 */
function get_espace_structure($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_structure', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}


/**
 * @param ressource $db
 * @param int $id_or_array
 * @return bobs_espace_point objet bobs_espace_point
 */
function get_espace_point($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_point', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @param ressource $db
 * @param int $id_or_array
 * @return bobs_espace_line
 */
function get_espace_line($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_line', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @param ressource $db
 * @param int $id_or_array
 * @return bobs_espace_polygon objet bobs_espace_polygon
 */
function get_espace_polygon($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_polygon', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @param ressource $db
 * @param int $id_or_array
 * @return bobs_espace_commune objet bobs_espace_commune
 */
function get_espace_commune($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_commune', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @param ressource $db
 * @param int $id_or_array
 * @return bobs_espace_departement objet bobs_espace_departement
 */
function get_espace_departement($db, $id_or_array) {
	static $mngr;
	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_departement', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}


/**
 * @return bobs_espace_chiro objet espace_chiro
 */
function get_espace_chiro($db, $id_or_array) {
    	static $mngr;

	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_chiro', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @return bobs_espace_l93_10x10
 */
function get_espace_l93_10x10($db, $id_or_array) {
    	static $mngr;

	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_l93_10x10', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @return bobs_espace_l93_5x5
 */
function get_espace_l93_5x5($db, $id_or_array) {
    	static $mngr;

	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_l93_5x5', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}


/**
 * @return bobs_espace_littoral
 */
function get_espace_littoral($db, $id_or_array) {
    	static $mngr;

	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_littoral', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @return bobs_espace_toponyme
 */
function get_espace_toponyme($db, $id_or_array) {
    	static $mngr;

	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_toponyme', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

/**
 * @return bobs_espace_ligne
 */
function get_espace_ligne($db, $id_or_array) {
    	static $mngr;

	if (!isset($mngr))
		$mngr = new bobs_single_mngr('bobs_espace_ligne', 'id_espace');
	try {
		return $mngr->get($db, $id_or_array);
	} catch (\Exception $e) {
		return null;
	}
}

function get_espace($db,$table,$id_espace) {
	switch ($table) {
		case 'espace_point':
			return get_espace_point($db, $id_espace);
		case 'espace_polygon':
			return get_espace_polygon($db, $id_espace);
		case 'espace_chiro':
			return get_espace_chiro($db, $id_espace);
		case 'espace_line':
			return get_espace_ligne($db, $id_espace);
		case 'espace_commune':
			return get_espace_commune($db, $id_espace);
		case 'espace_departement':
			return get_espace_departement($db, $id_espace);
		case 'espace_toponyme':
			return get_espace_toponyme($db, $id_espace);
		case 'espace_littoral':
			return get_espace_littoral($db, $id_espace);
		case 'espace_structure':
			return get_espace_structure($db, $id_espace);
		case 'espace_l93_10x10':
			return get_espace_l93_10x10($db, $id_espace);
	}
	throw new \Exception("pas possible avec table:$table id_espace:$id_espace");
}
