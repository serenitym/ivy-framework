<?php
    //$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
?>

<form action='' method='post'  style='text-align: center;'>
    <span>User type: </span>
    <select name='cid'>
        <option value='2'>Regular user</option>
        <option value='3'>Editor</option>
        <option value='4'>Publisher</option>
    </select>
    <br>
    <br>
    <input type='text' name='email' placeholder='email'  class='ivy-light ivy-padded'  />
    <br>

    <input type='hidden' name='modName' value='user' />
    <input type='hidden' name='methName' value='inviteUser' />
    <input type='hidden' name='sid' value='<?php echo session_id(); ?>' />
    <br>
    <input type='submit' name='inviteMember' value='Invite' class='ivy' />

</form>
