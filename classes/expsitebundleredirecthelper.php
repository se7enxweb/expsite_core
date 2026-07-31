<?php
class expSiteBundleRedirectHelper
{
    public function checkRedirect( eZContentObjectTreeNode $location )
    {
        $object = $location->attribute( 'object' );
        if ( !$object instanceof eZContentObject )
            return false;

        $dataMap = $object->dataMap();

        // Internal redirect
        if ( isset( $dataMap['internal_redirect'] ) )
        {
            $attr = $dataMap['internal_redirect'];
            if ( $attr instanceof eZContentObjectAttribute && $attr->hasContent() )
            {
                $relatedId = (int)$attr->attribute( 'data_int' );
                if ( $relatedId > 0 )
                {
                    $relatedObject = eZContentObject::fetch( $relatedId );
                    if ( $relatedObject instanceof eZContentObject )
                    {
                        $mainNodeId = (int)$relatedObject->attribute( 'main_node_id' );
                        if ( $mainNodeId !== (int)$location->attribute( 'node_id' ) && $mainNodeId > 0 )
                        {
                            $node = eZContentObjectTreeNode::fetch( $mainNodeId );
                            if ( $node instanceof eZContentObjectTreeNode )
                                return '/' . $node->attribute( 'url_alias' );
                        }
                    }
                }
            }
        }

        // External redirect
        if ( isset( $dataMap['external_redirect'] ) )
        {
            $attr = $dataMap['external_redirect'];
            if ( $attr instanceof eZContentObjectAttribute && $attr->hasContent() )
            {
                $url = $this->getUrlFromAttribute( $attr );
                if ( $url !== '' )
                {
                    if ( $this->isAbsoluteUrl( $url ) )
                        return $url;

                    $rootNode = eZContentObjectTreeNode::fetch( (int)eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
                    $rootUrl = $rootNode instanceof eZContentObjectTreeNode ? '/' . $rootNode->attribute( 'url_alias' ) : '/';

                    return rtrim( $rootUrl, '/' ) . '/' . ltrim( $url, '/' );
                }
            }
        }

        return false;
    }

    protected function getUrlFromAttribute( eZContentObjectAttribute $attribute )
    {
        $datatype = $attribute->attribute( 'data_type_string' );

        if ( $datatype === 'ezurl' )
        {
            $urlId = (int)$attribute->attribute( 'data_int' );
            if ( $urlId <= 0 )
                return '';

            $urlObject = eZURL::fetch( $urlId );
            return $urlObject instanceof eZURL ? (string)$urlObject->attribute( 'url' ) : '';
        }

        return trim( (string)$attribute->toString() );
    }

    protected function isAbsoluteUrl( $url )
    {
        return strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0;
    }
}
