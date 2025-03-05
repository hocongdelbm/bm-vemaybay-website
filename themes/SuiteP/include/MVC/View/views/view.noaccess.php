<?php
class ViewNoaccess extends SugarView
{
    public $type = 'noaccess';
    
    /**
     * @see SugarView::display()
     */
    public function display()
    {
        echo '<p class="error">Không có quyền truy cập nội dung này.</p>';
    }
}
