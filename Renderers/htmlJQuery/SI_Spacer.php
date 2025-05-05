<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * SI_Spacer - Creates a blank gap/space in the UI
 * 
 * This class extends SI_Item to create a simple spacer element
 * that adds both vertical and horizontal spacing.
 */
class SI_Spacer extends SI_Item {
    /**
     * Constructor for the spacer
     * 
     * @param string|int $width The width of the spacer (can be px, em, rem, etc.)
     * @param string|int $height The height of the spacer (can be px, em, rem, etc.)
     */
    function __construct( $width = '1rem', $height = '1rem' ) {
        $this->addClass('SI_Spacer');
        
        // Store dimensions as attributes
        $this->addAttribute('data-width', $width);
        $this->addAttribute('data-height', $height);
        
        // Add CSS styles for the spacer
        $this->addStylesToAutoCSS('
            .SI_Spacer {
                display: block;
            }
        ');
    }

    /**
     * Set the HTML tags for rendering
     * 
     * @param array $renderVals Optional rendering values
     */
    function setTagsVals($renderVals = null) {
        $width = $this->getAttribute('data-width');
        $height = $this->getAttribute('data-height');
        
        $this->start = '<div ' . $this->attributesString() . ' style="width:' . $width . '; height:' . $height . ';">';
        $this->end = "</div>\n";
    }
}
