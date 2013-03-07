<?php
//
// Description
// -----------
// This function will return a list of categories for the web galleries, 
// along with the images for each category highlight.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure.
// business_id:		The ID of the business to get events for.
//
// Returns
// -------
// <categories>
// 		<category name="Portraits" image_id="349" />
// 		<category name="Landscape" image_id="418" />
//		...
// </categories>
//
function ciniki_gallery_web_categories($ciniki, $settings, $business_id) {

	$strsql = "SELECT DISTINCT album AS name "
		. "FROM ciniki_gallery "
		. "WHERE ciniki_gallery.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND (ciniki_gallery.webflags&0x01) = 0 "
		. "AND album <> '' "
		. "ORDER BY album "
		. "";
	
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.gallery', array(
		array('container'=>'categories', 'fname'=>'name', 'name'=>'category',
			'fields'=>array('name')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['categories']) ) {
		return array('stat'=>'ok');
	}
	$categories = $rc['categories'];

	//
	// Load highlight images
	//
	foreach($categories as $cnum => $cat) {
		//
		// Look for the highlight image, or the most recently added image
		//
		$strsql = "SELECT ciniki_gallery.image_id, ciniki_images.image "
			. "FROM ciniki_gallery, ciniki_images "
			. "WHERE ciniki_gallery.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND album = '" . ciniki_core_dbQuote($ciniki, $cat['category']['name']) . "' "
			. "AND ciniki_gallery.image_id = ciniki_images.id "
			. "AND (ciniki_gallery.webflags&0x01) = 0 "
			. "ORDER BY (ciniki_gallery.webflags&0x10) DESC, ciniki_gallery.date_added DESC "
			. "LIMIT 1";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.gallery', 'image');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['image']) ) {
			$categories[$cnum]['category']['image_id'] = $rc['image']['image_id'];
		}
	}

	return array('stat'=>'ok', 'categories'=>$categories);	
}
?>