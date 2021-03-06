<?php

/**
 * Class Ecocode_Profiler_AbstractController
 */
class Ecocode_Profiler_Controller_AbstractController
    extends Mage_Core_Controller_Front_Action
{
    /** @var  Ecocode_Profiler_Model_Profiler */
    protected $profiler;

    public function preDispatch()
    {
        if (!Mage::app() instanceof Ecocode_Profiler_Model_AppDev) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
        }

        Mage::app()->getConfig()->setNode('frontend/events/core_block_abstract_to_html_before/observers/ecocode_profiler_context/type', 'disabled');
        Mage::app()->getConfig()->setNode('frontend/events/core_block_abstract_to_html_after/observers/ecocode_profiler_context/type', 'disabled');
        parent::preDispatch();
        return $this;
    }

    public function getProfiler()
    {
        if (!$this->profiler) {
            $this->profiler = Mage::getSingleton('ecocode_profiler/profiler');
        }
        return $this->profiler;
    }
}