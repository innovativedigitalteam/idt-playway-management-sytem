<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* 
*/
class IdtUsersClass
{
	
	function __construct()
	{
		add_action( 'admin_menu', array($this,'idt_admin_menu') );
	}
	public function idt_admin_menu()
	{	
		add_menu_page( 'Add Students', 'Add student', 'manage_options', 'idt-add-student', array($this, 'idt_add_user_display'),'',5 );
	}
	public function idt_add_user_display()
	{
		echo "here";
	}
}
new IdtUsersClass;