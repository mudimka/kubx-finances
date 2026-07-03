<?php
use \Bitrix\Main\Config\Option;

class CModuleOptions
{
    private $module_id = '';
    private $arTabs = [];
    private $arGroups = [];
    private $arOptions = [];
    public $request = [];
    public $defaultOptions = [];

    function __construct($module_id, $arTabs, $arGroups, $arOptions)
    {
        $this->module_id = $module_id;
        $this->arTabs = $arTabs;
        $this->arGroups = $arGroups;
        $this->arOptions = $arOptions;
        $this->request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
        $this->defaultOptions = \Bitrix\Main\Config\Option::getDefaults($this->module_id);

        //сохраняем отправленные данные
        if($this->request->isPost() && $this->request['Update'] && check_bitrix_sessid()){
            $this->saveOptions();
        }

        //заполняем дефолтными значениями (после сохранения, т.к. отправленные данные затирают дефолтные, если пустая строка)
        $this->initializeValues();
    }

    private function initializeValues(){
        foreach ($this->arOptions as $arOption)
        {
            $name = $arOption['NAME'];
            $value = Option::get('kubx.settings', $name);

            $default = $this->defaultOptions[$name];
            if (!$value && $default){
                Option::set('kubx.settings', $name, $default);
            }
        }
    }

    private function saveOptions()
    {
        foreach ($this->arOptions as $arOption)
        {
            if (!is_array($arOption))
                continue;

            if ($arOption['note'])
                continue;

            __AdmSettingsSaveOption($this->module_id, [$arOption['NAME']]);
        }
    }

    public function showOptions()
    {

        global $APPLICATION;

        $tabControl = new CAdminTabControl('tabControl', $this->arTabs);
        $tabControl->Begin();

        echo '<form method="post" action="' . $APPLICATION->GetCurPage() . '?mid=' . htmlspecialcharsbx($this->request['mid']) . '&amp;lang=' . $this->request['lang'] .'" name="kubx_settings">';

        foreach ($this->arTabs as $arTab){
            $tabControl->BeginNextTab();

            $aTabGroups = array_filter($this->arGroups, function($arGroup) use ($arTab) {
                return $arGroup['TAB'] == $arTab['DIV'];
            });
            foreach ($aTabGroups as $key => $aTabGroup){
                echo '<tr class="heading"><td colspan="2">'.$aTabGroup['TITLE'].'</td></tr>';

                $arTabOptions = array_filter($this->arOptions, function($arOption) use ($key) {
                    return $arOption['GROUP'] == $key;
                });

                array_multisort(array_column($arTabOptions, 'SORT'), SORT_ASC, $arTabOptions);
                foreach ($arTabOptions as $arTabOption){
                    self::echoOptionHTML($arTabOption);
                }
            }
        }

        $tabControl->BeginNextTab();
        $tabControl->Buttons();
        echo '<input type="submit" name="Update" value="' . GetMessage('MAIN_SAVE').'">
            <input type="reset" name="reset" value="' . GetMessage('MAIN_RESET') . '">' . bitrix_sessid_post();
        echo '</form>';
        $tabControl->End();

    }

    private static function echoOptionHTML($arTabOption){
        $name = $arTabOption['NAME'];
        $title = $arTabOption['TITLE'];

        $hint = $arTabOption['HINT'];
        $hintHTML = ($hint ? '<div class="adm-detail-content-cell-note" style="margin-top: 10px; opacity: 50%">'.$hint.'</div>' : '');

        $value = Option::get('kubx.settings', $name);

//        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

        switch($arTabOption['TYPE'])
            {
                case 'CHECKBOX':
                    $checked = $value === "Y" ? "checked" : "";
                    echo
'<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="    vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display:flex;">
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
            <input type="checkbox" name="checkbox" value="Y"'. $checked .'>
        </div>
        '.$hintHTML.'
    </td>
    <script>
        const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
        const checkbox_'.$name.' = document.querySelector("div#'.$name.' input[name=\'checkbox\']");
        checkbox_'.$name.'.addEventListener("change", (event)=> {
            value_'.$name.'.value = event.target.checked ? event.target.value : "N";
        });
    </script>
</tr>';
                   break;

                case 'SELECT':
                    $options = array_reduce($arTabOption['OPTIONS'],
                        function ($res, $option) use ($value) {
                            $checked = $option['VALUE'] === $value ? "selected" : "";
                            $res .='<option value="'.$option['VALUE'].'"'.$checked.'>'.$option['TITLE'].'</option>';
                            return $res;
                        }, ''
                    );
                    echo
'<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="    vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display:flex;">
            <select name="'.$name.'">
                '.$options.'
            </select>
        </div>
        '.$hintHTML.'
    </td>
</tr>';

                    break;

                case 'M_SELECT':
                    $values = explode("<>", $value);
                    $options = array_reduce($arTabOption['OPTIONS'],
                        function ($res, $option) use ($values) {
                            $checked = in_array($option['VALUE'], $values) ? "selected" : "";
                            $res .='<option value="'.$option['VALUE'].'"'.$checked.'>'.$option['TITLE'].'</option>';
                            return $res;
                        }, ''
                    );
                    echo
'<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="    vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display:flex;">
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
            <select name="select" multiple>
                '.$options.'
            </select>
        </div>
        '.$hintHTML.'
    </td>
    <script>
        const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
        const select'.$name.' = document.querySelector("div#'.$name.' select[name=\'select\']")
        select'.$name.'.addEventListener("change", (event)=> {
            const newVal = Array.from(event.target.selectedOptions)
                .map(option => option.value)
                .join("<>");
            value_'.$name.'.value = newVal;
        });
    </script>
</tr>';

                    break;

                case 'COLOR':
                    echo
'<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="    vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display:flex; gap: 5px;">
            <div style="position: relative;">
                <input type="text" name="'.$name.'" pattern="#[\dA-Za-z]{6}" value="'.$value.'">
                <span class="tooltip">
                    Введите значение в формате "#FFFFFF"
                </span>
            </div>
            <input type="color" name="colorpicker" value="'.$value.'">
        </div>
        '.$hintHTML.'
    </td>
    <script>
        const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
        const colorpicker_'.$name.' = document.querySelector("div#'.$name.' input[name=\'colorpicker\']")
        colorpicker_'.$name.'.addEventListener("change", (event)=> {
            value_'.$name.'.value = event.target.value;
        });
        value_'.$name.'.addEventListener("input", (event)=> {
            colorpicker_'.$name.'.value = event.target.value;
        });
    </script>
    <style>
        #'.$name.' input[type="text"]:invalid {
            border: red solid 1.5px;
        }
        #'.$name.' input[type="text"] + .tooltip {
            border: red solid 1.5px;
            color: red;
            background: white;
            padding: 10px; 
            border-radius: 10px; 
            width: max-content;
            position: absolute; 
            top: -20%;
            left: calc(100% + 5px);
            z-index: 1; 
           
            display: none;
        }
        #'.$name.' input[type="text"]:invalid + .tooltip {
            display: block;
        }
    </style>
</tr>';
                    break;

                case 'IMAGE':
                    $display = !$value ? 'display: none;' : 'display: flex;';
                    echo
'<tr valign="top">
   <td class="adm-detail-valign-top adm-detail-content-cell-l" ">
    '.$title.':
    </td>
    <td>
        <div id="'.$name.'" style="display: flex; flex-direction: column">
            <div style="width: max-content">
                <input type="file" accept="image/*, i" class="file">
            </div>
            
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
            <div class="img_delete_box" style="margin-top: 5px; background-color: #e0e8ea; padding: 5px; ' . $display . ' flex-direction: column; align-items: center">
                <img style="width: 50%; margin-top: 10px;" src="'.$value.'" alt="'.$value.'">
                <input type="button" value="Удалить логотип" style="width: 50%; margin-top: 10px; padding:5px;" class="delete">
            </div>
        </div>
         '.$hintHTML.'
    </td>
    <script>
        const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
        const input_'.$name.' = document.querySelector("div#'.$name.' input.file");
        const img_delete_box'.$name.' = document.querySelector("div#'.$name.' .img_delete_box");
        const img_'.$name.'  = document.querySelector("div#'.$name.' img");
        const delete_'.$name.' = document.querySelector("div#'.$name.' input.delete");
   
        input_'.$name.'.addEventListener("change", async (event)=> {
            const formData = new FormData();
            formData.append("file", event.target.files[0]);
            const res = await BX.ajax.runComponentAction("kubx", "uploadFiles", {
                mode: "class",
                data: formData
            }).then(response => response.data);
            value_'.$name.'.value = res.pathes[0];
            img_'.$name.'.src = res.pathes[0];
            img_delete_box'.$name.'.style.display = "flex";
        });
        if(delete_'.$name.'){
            delete_'.$name.'.addEventListener("click", () => {
                value_'.$name.'.value = "";
                img_'.$name.'.src = "";
                img_delete_box'.$name.'.style.display = "none";
                document.querySelector("div#'.$name.' div span span").textContent = "Добавить файл";
                input_'.$name.'.value = "";
            });
        }
    </script>
</tr>';
                    break;

                case 'M_IMAGE':
                    $values = strlen($value) ? explode('<>', $value) : [];
                    $imagesHTML = '';
                    foreach ($values as $val) {
                        $imagesHTML .= '
                                <div class="img_delete_box" draggable="true">
                                    <img  src="'.$val.'" alt="'.$val.'">
                                    <input type="button" value="Удалить"  class="delete">
                                </div>
                            ';
                    }
                    echo
'<tr valign="top">
    <td class="adm-detail-valign-top adm-detail-content-cell-l" >
        '.$title.':
    </td> 
    <td>
        <div id="'.$name.'" style="display: flex; flex-direction: column">
            <div>
                <input type="file" accept="image/*, i" class="files" multiple>
            </div>
            
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
            
            <div class="images">'.$imagesHTML.'</div>
        </div>
        '.$hintHTML.'
    </td>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelector("div#'.$name.' div span span").textContent = "Добавить файлы";
            
            const imagesContainer = document.querySelector("#'.$name.' .images");

            let draggedElement = null;
            
            imagesContainer.addEventListener("dragstart", function(event) {
                draggedElement = event.target.closest(".img_delete_box");
                event.dataTransfer.setData("text/plain", ""); // Needed for Firefox
            });
            
            imagesContainer.addEventListener("dragover", function(event) {
                event.preventDefault();
                const nearestElement = getNearestElement(event.clientX, event.clientY);
                const box = nearestElement.getBoundingClientRect();
                const mouseY = event.clientY;
                const offset = mouseY - box.top - box.height / 2;
                if (offset < 0) {
                    nearestElement.parentNode.insertBefore(draggedElement, nearestElement);
                } else {
                    nearestElement.parentNode.insertBefore(draggedElement, nearestElement.nextElementSibling);
                }
            });
            
            function getNearestElement(x, y) {
                const elements = Array.from(imagesContainer.querySelectorAll(".img_delete_box"));
                return elements.reduce((nearestElement, currentElement) => {
                    const box = currentElement.getBoundingClientRect();
                    const offsetX = x - box.left - box.width / 2;
                    const offsetY = y - box.top - box.height / 2;
                    const distance = Math.hypot(offsetX, offsetY);
                    if (distance < nearestElement.distance) {
                        return { distance, element: currentElement };
                    } else {
                        return nearestElement;
                    }
            }, { distance: Number.POSITIVE_INFINITY }).element;
            }
            
            imagesContainer.addEventListener("dragend", function() {
                draggedElement = null;
                // Вызов вашей функции после перетаскивания
                updateValue_'.$name.'();
            });
        });
        
        const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
        const input_'.$name.' = document.querySelector("div#'.$name.' input.files");
        const images_'.$name.'_block = document.querySelector("div#'.$name.' div.images");
        
        input_'.$name.'.addEventListener("change", async (event)=> {
            const formData = new FormData();
            for (let i = 0; i < event.target.files.length; i++) {
                formData.append("files_"+i, event.target.files[i]);
            }
            const res = await BX.ajax.runComponentAction("kubx", "uploadFiles", {
                mode: "class",
                data: formData
            }).then(response => response.data);
            
            res.pathes.forEach((path) => {
                images_'.$name.'_block.innerHTML += `
                    <div class="img_delete_box" draggable="true">
                        <img  src="${path}" alt="${path}">
                        <input type="button" value="Удалить" class="delete">
                    </div>
                `;
            });

            updateValue_'.$name.'();
            initDeletes_'.$name.'();
            document.querySelector("div#'.$name.' div span span").textContent = "Добавить файлы";
        });
        
        const initDeletes_'.$name.' = () => {
            const buttonsDelete = images_'.$name.'_block.querySelectorAll(".delete");
            buttonsDelete.forEach((buttonDelete) => {
                buttonDelete.onclick = (event) => {
                    event.target.parentNode.remove();
                    updateValue_'.$name.'();
                    document.querySelector("div#'.$name.' div span span").textContent = "Добавить файлы";
                };
            })
        }
        
        const updateValue_'.$name.' = () => {
            const imgs = images_'.$name.'_block.querySelectorAll("img");
            const arVals = [];

            imgs.forEach((img) => {
                arVals.push(img.alt);
            })
            
            value_'.$name.'.value = arVals.join("<>");
        }

        initDeletes_'.$name.'();
    </script>
    <style>
        #'.$name.' .images {
            padding: 0 5px; 
            margin-top: 5px;
            background-color: #e0e8ea;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }
        
        #'.$name.' .img_delete_box {
            display: flex; 
            flex-direction: column; 
            align-items: center;
            padding: 5px 5px 10px; 
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            cursor: move;
        }
        
         #'.$name.' .img_delete_box:nth-child(2n) {
            border-right: none;
        }
        
        #'.$name.' .img_delete_box img {
            width: 100%;
            margin: auto;
        }
        
        #'.$name.' .img_delete_box input {
            width: 100%;
            margin-top: auto;
        }
    </style>
</tr>';
                    break;

                case 'FILE':
                    $display = !$value ? 'display: none;' : 'display: flex;';
                    echo
'<tr valign="top">
    <td class="adm-detail-valign-top adm-detail-content-cell-l" >
        '.$title.':
    </td>
    <td>
        <div id="'.$name.'" style="display: flex; flex-direction: column">
            <div style="width: max-content">
                <input type="file" class="file">
            </div>
            
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
            <div class="img_delete_box" style="margin-top: 5px; background-color: #e0e8ea; padding: 5px; ' . $display . ' flex-direction: column; align-items: center">
                <div>'.$value.'</div>
                <input type="button" value="Удалить" style="width: 50%; margin-top: 10px;" class="delete">
            </div>
        </div>
        '.$hintHTML.'
    </td>
    <script>
        const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
        const input_'.$name.' = document.querySelector("div#'.$name.' input.file");
        const img_delete_box'.$name.' = document.querySelector("div#'.$name.' .img_delete_box");
        const img_'.$name.'  = document.querySelector("div#'.$name.' img");
        const delete_'.$name.' = document.querySelector("div#'.$name.' input.delete");
   
        input_'.$name.'.addEventListener("change", async (event)=> {
            const formData = new FormData();
            formData.append("file", event.target.files[0]);
            const res = await BX.ajax.runComponentAction("kubx", "uploadFiles", {
                mode: "class",
                data: formData
            }).then(response => response.data);
            value_'.$name.'.value = res.pathes[0];
            img_'.$name.'.src = res.pathes[0];
            img_delete_box'.$name.'.style.display = "flex";
        });
        if(delete_'.$name.'){
            delete_'.$name.'.addEventListener("click", () => {
                value_'.$name.'.value = "";
                img_'.$name.'.src = "";
                img_delete_box'.$name.'.style.display = "none";
                document.querySelector("div#'.$name.' div span span").textContent = "Добавить файл";
                input_'.$name.'.value = "";
            });
        }
    </script>
</tr>';
                    break;

                case 'KEY_VALUE':
                    [$key, $keyValue] = strlen($value) ? explode('::', $value) : [];

                    $key = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
                    $keyValue = htmlspecialchars($keyValue, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
                    echo '
<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display: flex; flex-direction: column">
            <div class="keyvalue-result">
                <input type="text" class="key" value="'.$key.'" placeholder="Ключ" style="width: 30%">
                <input type="text" class="value" value="'.$keyValue.'" placeholder="Значение" style="width: 50%"> 
            </div>
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
        </div> 
        '.$hintHTML.'
    </td>
<script>
    const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
    const keyvalues_'.$name.'_block = document.querySelector("div#'.$name.' div.keyvalue-result");
    
    const initUpdate_'.$name.' = () => {
        const inputs = keyvalues_'.$name.'_block.querySelectorAll("input");
        inputs.forEach((input) => {
            input.onchange = () => {
                updateValue_'.$name.'();
            };
        })
    }
   
    const updateValue_'.$name.' = () => {
        let key = keyvalues_'.$name.'_block.querySelector("input.key").value;
        let value = keyvalues_'.$name.'_block.querySelector("input.value").value;

        if (key != "" && value != "") {
                value_'.$name.'.value = key+"::"+value;
        }
    }
    initUpdate_'.$name.'();
</script>
</tr>
    ';
                break;

                case 'M_KEY_VALUE':
                    $res = strlen($value) ? explode('<>', $value) : [];
                    if (!empty($res)) {
                        //нужно чтобы не ломалась верстка из-за кавычек в value, value нельзя менять в начале, так как тогда не будет работать explode
                        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

                        $values = '';
                        foreach ($res as &$val) {
                            [$key, $keyValue] = explode('::', $val);

                            $key = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
                            $keyValue = htmlspecialchars($keyValue, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

                            $values .= '
                            <div class="keyvalue-box" style="margin-bottom: 5px">
                                <input type="text" class="key" value="'.$key.'" placeholder="Ключ" style="width: 30%">
                                <input type="text" class="value" 
                                value="'.$keyValue.'"
                                placeholder="Значение" style="width: 50%"> 
                                <input type="button" value="x"  class="delete" vertical-align: middle>
                            </div>
                        ';
                        }
                    } else {
                        $values = '
                            <div class="keyvalue-box" style="margin-bottom: 5px">
                                <input type="text" class="key" value="" placeholder="Ключ" style="width: 30%">
                                <input type="text" class="value" value="" placeholder="Значение" style="width: 50%"> 
                                <input type="button" value="x"  class="delete" vertical-align: middle>
                            </div>
                        ';
                    }

                    echo '
<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display: flex; flex-direction: column">
            <div class="keyvalues-result">'.$values.'</div>
            <div>
                <input class="add-value" type="button" value="Добавить">
            </div>
            <input type="text" style="display: none" name="'.$name.'" value="'.$value.'">
        </div>
        '.$hintHTML.'
    </td>
<script>
    const value_'.$name.' = document.querySelector("div#'.$name.' input[name=\''.$name.'\']");
    const button_'.$name.'_add = document.querySelector("div#'.$name.' input.add-value");
    const keyvalues_'.$name.'_block = document.querySelector("div#'.$name.' div.keyvalues-result");
    
    button_'.$name.'_add.addEventListener("click", function (event) {
        document.querySelector("div#'.$name.' div.keyvalues-result").insertAdjacentHTML("beforeend",`
            <div class="keyvalue-box" style="margin-bottom: 5px">
                <input type="text" class="key" value="" placeholder="Ключ" style="width: 30%">
                <input type="text" class="value" value="" placeholder="Значение" style="width: 50%"> 
                <input type="button" value="x"  class="delete" vertical-align: middle>
            </div>
        `);
        initDeletes_'.$name.'();
        initUpdate_'.$name.'();
    });
    
    const initUpdate_'.$name.' = () => {
        const inputs = keyvalues_'.$name.'_block.querySelectorAll("div .keyvalue-box input");
        inputs.forEach((input) => {
            input.onchange = () => {
                updateValue_'.$name.'();
            };
        })
    }
    
    const initDeletes_'.$name.' = () => {
        const buttonsDelete = keyvalues_'.$name.'_block.querySelectorAll(".delete");
        buttonsDelete.forEach((buttonDelete) => {
            buttonDelete.onclick = (event) => {
                event.target.parentNode.remove();
                updateValue_'.$name.'();
            };
        })
    }
    
    const updateValue_'.$name.' = () => {
        const vals = keyvalues_'.$name.'_block.querySelectorAll("div .keyvalue-box");
        const arVals = [];

        vals.forEach((val) => {
            var key = val.querySelector("input.key").value;
            var value = val.querySelector("input.value").value;
            
            if (key != "" && value != "") {
                arVals.push(key+"::"+value);
            }
        })
        
        value_'.$name.'.value = arVals.join("<>");
    }
    
    initUpdate_'.$name.'();
    initDeletes_'.$name.'();
</script>
</tr>
    ';
                break;

                case 'TEXTAREA':
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

                    $rows = $arTabOption['ROWS_COUNT'];
                    echo
'<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="    vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display:flex;">
            <textarea type="text" name="'.$name.'" rows="'.$rows.'" style="width: 95%; resize: none;">'.$value.'</textarea>
        </div>
        '.$hintHTML.'
    </td>
</tr>';
                break;

                case 'UPDATE_CONSTANTS':
                    echo
                        '
<tr>
    <td colspan="2">
        <div id="'.$name.'" style="display: flex; justify-content: center">
            <input type="button" value="Обновить константы" style="width: max-content;" class="update">
        </div>
        '.$hintHTML.'
    </td>
</tr>
<script>
    const button_'.$name.' = document.querySelector("div#'.$name.' input.update");
    
    button_'.$name.'.addEventListener("click", (event)=> {
        button_'.$name.'.disabled = true;
        button_'.$name.'.value = "Загрузка...";

        BX.ajax({
            url: "/bitrix/components/kubx/updateConstants.php",
            type: "GET",
            data: {},
            dataType: "json",
            
            onsuccess: function (data) {
                if (data.result === "success") {
                    alert("Константы успешно обновлены");
                } else {
                    alert("Ошибка при обновлении констант");
                }
                button_'.$name.'.disabled = false;
                button_'.$name.'.value = "Обновить константы";
            },
            onfailure: function (error) {
                alert("Произошла ошибка");
                button_'.$name.'.disabled = false;
                button_'.$name.'.value = "Обновить константы";
            },
        });

    });
</script>
                        ';
                    break;

                case 'TEXT':
                default:
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

                    echo
'<tr>
    <td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%" style="    vertical-align: middle;">
        '.$title.':
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <div id="'.$name.'" style="display:flex;">
            <input type="text" name="'.$name.'" value="'.$value.'" style="width: 95%;">
        </div>
        '.$hintHTML.'
    </td>
</tr>';
                    break;
            }
    }
}
?>
