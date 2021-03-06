<?php
//
// Description
// -----------
// This function will return the list of options for the module that can be set for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get options for.
//
// args:            The possible arguments for profiles
//
//
// Returns
// -------
//
function ciniki_gallery_hooks_webOptions(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.gallery']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.gallery.1', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    //
    // Get the settings from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid', $tnid, 'ciniki.web', 'settings', 'page-gallery');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }

    $pages['ciniki.gallery'] = array('name'=>'Gallery', 'options'=>array(
        array('label'=>'Image Quality',
            'setting'=>'page-gallery-image-quality', 
            'type'=>'toggle',
            'value'=>(isset($settings['page-gallery-image-quality'])?$settings['page-gallery-image-quality']:'no'),
            'toggles'=>array(
                array('value'=>'regular', 'label'=>'Regular'),
                array('value'=>'high', 'label'=>'High'),
                ),
            ), 
/*        array('label'=>'Sidebar',
            'setting'=>'page-artistprofiles-sidebar', 
            'type'=>'toggle',
            'value'=>(isset($settings['page-artistprofiles-sidebar'])?$settings['page-artistprofiles-sidebar']:'no'),
            'toggles'=>array(
                array('value'=>'no', 'label'=>'No'),
                array('value'=>'yes', 'label'=>'Yes'),
                ),
            ), */
        ));

    return array('stat'=>'ok', 'pages'=>$pages);
}
?>
