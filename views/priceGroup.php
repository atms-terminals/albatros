<?php
/**
 * Получение уровня в меню
 *
 * @param int $id Уровень меню
 * @return string html-код списка;
 */
function getMenuLevel($menu, $id)
{
    $html = '';
    if (!empty($menu[$id])) {
        $i = 0;
        foreach ($menu[$id] as $key => $item) {
            $i++;
            $status = $item['status'] ? 'checked' : '';
            $checkedDanger = $item['color'] == 'danger' ? 'checked' : '';
            $checkedSuccess = $item['color'] == 'success' ? 'checked' : '';
            $checkedWarning = $item['color'] == 'warning' ? 'checked' : '';
            $checkedPrimary = $item['color'] == 'primary' ? 'checked' : '';

            $dropDown = (!empty($menu[$item['id']])) ? "<button class='dropdown'><span class='glyphicon glyphicon-triangle-top' aria-hidden='true'></span></button>" : '';

            $html .= "<li>$dropDown<input class='serviceItem id' type='checkbox' id='{$item['id']}' $status title='запретить/разрешить'>
                {$item['desc']} (id={$item['id']}) 
                <button class='confirmDelete price'><span class='glyphicon glyphicon-remove' title='Удалить' data-toggle='modal' data-target='#confirmDeleteDialog'></span></button><br>

                <span class='color btn btn-danger'><input type='radio' $checkedDanger name='color$id$i' value='danger' ></span>
                <span class='color btn btn-success'><input type='radio' $checkedSuccess name='color$id$i' value='success' ></span>
                <span class='color btn btn-warning'><input type='radio' $checkedWarning name='color$id$i' value='warning' ></span>
                <span class='color btn btn-primary'><input type='radio' $checkedPrimary name='color$id$i' value='primary' ></span>
                <input type='text' value='{$item['clients_desc']}' class='clientsDesc' size='50' placeholder='Название для терминала' title='Название для терминала' />";
            if (empty($menu[$item['id']])) {
                // $html .= "<input type='text' value='{$item['price']}' class='price' size='8' placeholder='Цена услуги' title='Цена услуги' />";
            } else {
                $html .= "<ul class='hidden'>";
                $html .= getMenuLevel($menu, $item['id']);
                $html .= "</ul>";
            }
            $html .= "</li>";
        }
    }
    return $html;
}

// создаем структуру меню
?>
<ul>
<?=getMenuLevel($list, 0);?>
</ul>
