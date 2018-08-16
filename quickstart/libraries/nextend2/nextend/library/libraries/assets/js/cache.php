<?php

class N2AssetsCacheJS extends N2AssetsCache {

    public $outputFileType = "js";

    protected $initialContent = '(function(){var N=this;N.N2_=N.N2_||{r:[],d:[]},N.N2R=N.N2R||function(){N.N2_.r.push(arguments)},N.N2D=N.N2D||function(){N.N2_.d.push(arguments)}}).call(window);';


    protected function createInlineCode($group, &$codes) {
        return N2AssetsJs::serveJquery(parent::createInlineCode($group, $codes));
    }

    /**
     * @param N2CacheManifest $cache
     *
     * @return string
     */
    public function getCachedContent($cache) {

        $content = '(function(){var N=this;N.N2_=N.N2_||{r:[],d:[]},N.N2R=N.N2R||function(){N.N2_.r.push(arguments)},N.N2D=N.N2D||function(){N.N2_.d.push(arguments)}}).call(window);';
        $content .= parent::getCachedContent($cache);
        $content .= "N2D('" . $this->group . "');";

        return $content;
    }
}