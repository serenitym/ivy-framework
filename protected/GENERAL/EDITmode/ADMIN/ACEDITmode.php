<?php
class ACEDITmode
{
    // can be altered in yml config if needed
    var $defaultInit = true;

    /**
     * Activeaza ediatea live
     * - include js-ul care permite editarea automata
     *
     * Modulul este instantiat automat de Ccore insa js-ul care faciliteaza
     * editarea automata nu este inclus decat daca defaultInit este true sau
     * daca userul a apasat butonul de activare
     *
     */
    function activateLiveEdit()
    {
        $this->C->Set_incFiles($this->modName, $this->modType, 'js', 'jsEditMode');
        $this->C->Set_incFiles($this->modName, $this->modType, 'css', 'cssEditMode');
    }
    /**
     * Retine faptul ca a fost activat live-edit
     */
    function activate()
    {
        $_SESSION['activeEdit'] =  true;
        $this->C->reLocate();
    }
    /**
     * Retine ca a fost dezactiva live-edit
     */
    function deactivate()
    {
        unset($_SESSION['activeEdit']);
        $this->C->reLocate();
    }
    /**
     * vede daca a fost activat live-edit , daca da include js-ul
     * refa / adauga butonul de activare / dezactivare pe toolbar
     */
    function setStatus()
    {
         if(isset($_SESSION['activeEdit'])) {
             $activeValue = "stop live edit";
             $activeMeth = "deactivate" ;
             $this-> activateLiveEdit();
         } else {
             $activeValue = "start live edit";
             $activeMeth = "activate" ;
         }
         isset($_SESSION['activeEdit']) ?  : "start live edit";
         isset($_SESSION['activeEdit']) ? "deactivate" : "activate";

         $this->C->TOOLbar->ADDbuttons(
            "<span>
                 <form method='post' action=''>
                    <input type='hidden' name='methName' value='{$activeMeth}'>
                    <input type='hidden' name='modName' value='EDITmode'>
                    <input type='submit' name='activeEdit' value='{$activeValue}'>
                 </form>
             </span>"
         );

     }
    /**
     * Daca by default avea live-edit => activeaza
     * daca nu vezi care este statusul din session si activeaza sau nu
     */
    function _init_()
    {
        if($this->defaultInit) {
            $this->activateLiveEdit();
        } else {
            $this->setStatus();
        }
    }

}
