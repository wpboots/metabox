<?php

$count = count($this->Boxes[$id]['sections']);
echo '<div class="boots-form boots-metabox' . ($count > 1 ? '' : ' boots-metabox-nosection') . '">';
foreach($Sections as $section => $Values)
{
    $hr = '';
    echo $count > 1 ? ('<h4>' . $section . '</h4>') : '';
    echo '<ul>';
    foreach($Values as $array_or_cb)
    {
        if(!is_array($array_or_cb))
        {
            echo '<li>';
            call_user_func_array($array_or_cb, array($Post, $Args));
        }
        else
        {
            $type = array_shift($array_or_cb);
            $A = array_shift($array_or_cb);

            if(($type == 'checkboxes') || !isset($A['name']) && isset($A['data']) && is_array($A['data']))
            {
                foreach($A['data'] as & $Checkbox)
                {
                    $Checkbox['id'] = !isset($Checkbox['id']) ? $Checkbox['name'] : $Checkbox['id'];
                    $Checkbox['value'] = !isset($Checkbox['value'])
                    ? $this->Boots->Database->term($Checkbox['name'])->get($Post->ID)
                    : $Checkbox['value'];
                    $Checkbox['name'] = 'boots_metabox_' . $Checkbox['name'] . '';
                }
            }
            else
            {
                $A['id'] = !isset($A['id']) ? $A['name'] : $A['id'];
                $A['value'] = !isset($A['value'])
                ? $this->Boots->Database->term($A['name'])->get($Post->ID)
                : $A['value'];
                //preg_match('^/boots_metabox\[(.*?)\]$/', $A['name'], $HiddenBoots);
                $A['name'] = ($type == 'hidden' && preg_match('/^boots_metabox\[.*?\]$/', $A['name']))
                ? $A['name'] : 'boots_metabox_' . $A['name'] . '';
            }

            echo '<li' . ($type == 'hidden' ? ' style="display: none;"' : '') . '>';
            echo $this->Boots->Form->generate($type, $A);
        }
        echo '</li>';
    }
    echo '</ul>';
}
echo '</div>';