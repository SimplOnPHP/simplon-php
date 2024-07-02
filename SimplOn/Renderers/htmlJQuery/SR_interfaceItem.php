<?php


class SR_interfaceItem extends SC_BaseObject
{
    function fillLayout($Layout) {
        return $Layout;
    }

    function render($output = null) {
		$Layout = $this->getLayout();
		$Layout = $this->fillLayout($Layout);

        global $rendr2;

        if($output){
            $output = new $output();
            //$output->message($SystemMessage);
            $output->content($Layout->htmlOuter());
            $outputtemplate = $rendr2->directlayoutPath($output, 'showView');
            $Layout = \phpQuery::newDocumentFileHTML($outputtemplate);
            $rendr2->getJSandCSS($Layout);
            $Layout = $rendr2->addCSSandJSLinksToTemplate($Layout);

            $Layout = $rendr2->fillDatasInElementLayout($output,'showView',$Layout);

        }

        return $Layout->html();
    }

}