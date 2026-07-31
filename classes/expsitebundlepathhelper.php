<?php
class expSiteBundlePathHelper
{
    public function getPath( $locationId, $options = array() )
    {
        $useAllContentTypes = isset( $options['use_all_content_types'] ) ? (bool)$options['use_all_content_types'] : false;
        $showCurrentLocation = isset( $options['show_current_location'] ) ? (bool)$options['show_current_location'] : false;
        $absoluteUrl = isset( $options['absolute_url'] ) ? (bool)$options['absolute_url'] : false;

        $excludedContentTypes = $this->excludedContentTypes();

        $rootLocationId = (int)eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );

        $node = eZContentObjectTreeNode::fetch( (int)$locationId );
        if ( !$node instanceof eZContentObjectTreeNode )
            return array();

        $path = $node->attribute( 'path_array' );
        if ( !is_array( $path ) )
            return array();

        // Remove the root tree node (1)
        array_shift( $path );

        $pathArray = array();
        $rootLocationFound = false;

        foreach ( $path as $pathItemId )
        {
            $pathItemId = (int)$pathItemId;

            if ( $pathItemId === $rootLocationId )
                $rootLocationFound = true;

            if ( !$rootLocationFound )
                continue;

            $location = eZContentObjectTreeNode::fetch( $pathItemId );
            if ( !$location instanceof eZContentObjectTreeNode )
                continue;

            if ( !$showCurrentLocation && $pathItemId === (int)$locationId )
                continue;

            $object = $location->attribute( 'object' );
            if ( !$object instanceof eZContentObject )
                continue;

            $classIdentifier = (string)$object->attribute( 'class_identifier' );

            if ( !$useAllContentTypes && in_array( $classIdentifier, $excludedContentTypes, true ) )
                continue;

            $disableItemUrl = $useAllContentTypes && in_array( $classIdentifier, $excludedContentTypes, true );

            $itemName = (string)$location->attribute( 'name' );

            $dataMap = $object->dataMap();
            if ( isset( $dataMap['breadcrumb_title'] ) && $dataMap['breadcrumb_title'] instanceof eZContentObjectAttribute )
            {
                $breadcrumbAttribute = $dataMap['breadcrumb_title'];
                if ( $breadcrumbAttribute->hasContent() )
                {
                    $value = $breadcrumbAttribute->content();
                    $itemName = is_string( $value ) ? $value : (string)$breadcrumbAttribute->toString();
                }
            }

            $url = false;
            if ( !$disableItemUrl && $pathItemId !== (int)$locationId )
            {
                $url = '/' . $location->attribute( 'url_alias' );
                if ( $absoluteUrl )
                {
                    $url = eZSys::serverURL() . eZSys::indexDir() . $url;
                }
            }

            $pathArray[] = array(
                'text' => $itemName,
                'url' => $url,
                'location' => $location,
            );
        }

        return $pathArray;
    }

    protected function excludedContentTypes()
    {
        $ini = eZINI::instance( 'expsite_core.ini' );
        if ( !$ini->hasVariable( 'PathHelper', 'ExcludedContentTypes' ) )
            return array();

        $value = $ini->variable( 'PathHelper', 'ExcludedContentTypes' );
        if ( !is_array( $value ) )
            return array();

        $value = array_filter( $value );
        return array_values( $value );
    }
}
