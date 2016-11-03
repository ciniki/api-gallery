<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the image belongs to.
// name:                The name of the image.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_gallery_imageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'gallery_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Exhibition Image'), 
        'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'album_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Album'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Website Flags'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'gallery', 'private', 'checkAccess');
    $rc = ciniki_gallery_checkAccess($ciniki, $args['business_id'], 'ciniki.gallery.imageUpdate', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    if( isset($args['name']) ) {
        //
        // Get the existing image details
        //
        $strsql = "SELECT uuid FROM ciniki_gallery "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['gallery_image_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.gallery', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.gallery.14', 'msg'=>'Gallery image not found'));
        }
        $item = $rc['item'];

        if( $args['name'] != '' ) { 
            $args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
        } else {
            // Use the UUID if image name is not specified
            $args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($item['uuid'])));
        }

        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, name, permalink FROM ciniki_gallery "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['gallery_image_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.gallery', 'image');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.gallery.15', 'msg'=>'You already have an image with this name, please choose another name'));
        }
    } 

    //
    // Check if album name is Uncategorized
    //
    if( isset($args['album']) && $args['album'] == 'Uncategorized' ) {
        $args['album'] = '';
    }

    //
    // Update the image
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.gallery.image', $args['gallery_image_id'], $args, 0x07);
}
?>
