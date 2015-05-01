<?php

$count = count($this->Boxes[$id]['sections']);
echo '<div class="boots-form boots-metabox' . ($count > 1 ? '' : ' boots-metabox-nosection') . '">';
foreach($Sections as $section => $Fields)
{
    echo $count > 1 ? ('<h4>' . $section . '</h4>') : '';
    echo '<ul>';
    foreach($Fields as $group => $Arr)
    {
        $type = isset($Arr['type'])
        ? $Arr['type'] : null;
        $Atts = isset($Arr['args'])
        ? $Arr['args'] : null;
        $Requires = isset($Arr['requires'])
        ? $Arr['requires'] : array();

        if($Atts && !is_array($Atts)) // its a custom field
        {
            $uniqueid = uniqid('', true);
            echo '<li data-id="' . $uniqueid . '">';
            if(is_callable($Atts))
            call_user_func($Atts);
            else echo '<i>' . $Atts . '</i> is not callable';
            if($Requires) include $this->dir . '/requires.php';
            echo '</li>';
        }
        else if($type && $Atts) // its a single field
        {
            $uniqueid = uniqid(isset($Atts['name']) ? ($Atts['name'] . '-') : '', true);
            echo '<li data-id="' . $uniqueid . '"';
            echo $type == 'hidden'
            ? ' style="display: none;">' : '>';
            if($type == '_') // its a custom callable field
            {
                if(is_callable($Atts))
                call_user_func($Atts);
                else echo '<i>' . $Atts . '</i> is not callable';
                echo  "\n";
            }
            else { // its a form field
				$Atts['value'] = !isset($Atts['value'])
                ? $this->Boots->Database->term($Atts['name'])->get($Post->ID)
                : $Atts['value'];
				$Atts['checked'] = !isset($Atts['checked'])
                ? $Atts['value'] == $this->Boots->Database->term($Atts['name'])->get($Post->ID)
                : $Atts['checked'];
                $Atts['id'] = !isset($Atts['id'])
                ? (isset($Atts['name']) ? $Atts['name'] : null)
                : $Atts['id'];
                $Atts['name'] = isset($Atts['name'])
                ? ('boots_metabox_' . $Atts['name'])
                : $Atts['name'];
                echo $this->Boots->Form->generate($type, $Atts) . "\n";
            }
            if($Requires) include $this->dir . '/requires.php';
            echo '</li>' . "\n";
        }
        else if(!$type) // its a group
        {
            $GroupProp = isset($Data['groups'][$group])
            ? $Data['groups'][$group]
            : array();
            echo '<li>';
            echo '<label>' . $group . '</label>';
            foreach($Arr as $GroupArr)
            {
                $type = isset($GroupArr['type'])
                ? $GroupArr['type'] : null;
                $Atts = isset($GroupArr['args'])
                ? $GroupArr['args'] : null;
                $Requires = isset($GroupArr['requires'])
                ? $GroupArr['requires'] : array();
                if($type && $Atts)
                {
                    $uniqueid = uniqid(isset($Atts['name']) ? ($Atts['name'] . '-') : '', true);
                    echo '<div data-id="' . $uniqueid . '" class="boots-form-group"';
                    echo $type == 'hidden'
                    ? ' style="display: none;">' : '>';
                    if($type == '_') // its a custom callable field
                    {
                        if(is_callable($Atts))
                        call_user_func($Atts);
                        else echo '<i>' . $Atts . '</i> is not callable';
                    }
                    else { // its a form field
						$Atts['value'] = !isset($Atts['value'])
		                ? $this->Boots->Database->term($Atts['name'])->get($Post->ID)
		                : $Atts['value'];
						$Atts['checked'] = !isset($Atts['checked'])
		                ? $Atts['value'] == $this->Boots->Database->term($Atts['name'])->get($Post->ID)
		                : $Atts['checked'];
		                $Atts['id'] = !isset($Atts['id'])
		                ? (isset($Atts['name']) ? $Atts['name'] : null)
		                : $Atts['id'];
		                $Atts['name'] = isset($Atts['name'])
		                ? ('boots_metabox_' . $Atts['name'])
		                : $Atts['name'];
                        echo $this->Boots->Form->generate($type, $Atts);
                    }
                    echo '</div>' . "\n";
                    if($Requires) include $this->dir . '/requires.php';
                }
            }
            echo isset($GroupProp['help'])
            ? ('<p>' . $GroupProp['help'] . '</p>')
            : '';
            echo '</li>' . "\n";
        }
    }
    echo '</ul>';
}
echo '</div>';
