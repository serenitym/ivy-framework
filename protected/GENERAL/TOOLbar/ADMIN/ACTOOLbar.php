<?php
class ACTOOLbar
{
    var $C;               //main object
    var $lang;
    var $lang2;
    var $dispPATH ='';

    var $buttons = array();

    public function ADDbuttons($buttonSTR)
    {
        array_push($this->buttons,$buttonSTR);
    }

    function DISPLAY()
    {
        //_____________________________________[Setting buttons]__________________
        //$disp = "<div id='admin_TOOLbar'>";
        $disp = "<div id='admin_TOOLbar' class=''>";
            foreach($this->buttons as $button)
                $disp .= "<span>{$button}</span>";
        $disp .="</div>";
        //________________________________________________________________________



        $disp .="<div id='admin_POPUP'>
                    <div id='CLOSE_admin_POPUP'>
                        <form action='".Toolbox::curURL()."' method='post'>
                                <input type='submit' name='close_adminPOPUP' value='close' class='Tbar_but' />
                        </form>
                        <input type='button' value='x' onclick=\"CLOSE_admin_POPUP()\" class='Tbar_but' />
                    </div>
                    {$this->statusPOPUP}
                    <div id='admin_POPUP_content'>
                    </div>
                 </div>";

        return $disp;
       // file_put_contents($this->dispPATH,$disp);
    }
    function __construct($C)
    {
        $this->C = &$C;

        $this->lang = &$C->lang;
        $this->lang2 = &$C->lang2;


        $currentPOPUP = (isset($_POST['currentPOPUP']) ? $_POST['currentPOPUP'] : '');
        $currentPOPUP_modeType = (isset($_POST['currentPOPUP_modeType']) ? $_POST['currentPOPUP_modeType'] : '');
        $this->statusPOPUP = "<input type='hidden' name='statusPOPUP' value='".$currentPOPUP."' />".
                             "<input type='hidden' name='statusPOPUP_modeType' value='".$currentPOPUP_modeType."' />" ;

        $this->uname = isset($_SESSION['auth']->uname) ? : 'admin';

        array_push( $this->buttons,
            "<a href='index.php?logOUT=1' id='logOUT'>Log out ({$this->uname})  </a>"
            );



       /* $this->dispPATH = publicPath.'PLUGINS/TOOLbar/RES/TOOLbar.html';
        if(!file_exists($this->dispPATH)) $this->getDISPLAY();*/
    }
}
