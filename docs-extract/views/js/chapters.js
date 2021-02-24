toolbar_2 = cell_1.attachToolbar();

toolbar_2.setIconset("awesome");
toolbar_2.addButton("select", 0, "<i class=\"fa fa-check-square-o\" aria-hidden=\"true\"></i>&nbsp; Select All");
toolbar_2.addSeparator("button_separator_1", 1);
toolbar_2.addButton("unselect", 2, "<i class=\"fa fa-square-o\" aria-hidden=\"true\"></i>&nbsp;Unselect All");
toolbar_2.addSeparator("button_separator_2", 3);
toolbar_2.addButton("update", 4, "<i class='fa fa-edit' aria-hidden='true'></i>&nbsp; Update");
toolbar_2.addSeparator("button_separator_3", 5);
toolbar_2.addButton("delete", 6, "<i class=\"fa fa-trash\" aria-hidden=\"true\"></i> &nbsp; Delete");
toolbar_2.attachEvent('onClick', onToolbar2Click);


grid_2 = cell_1.attachGrid();
grid_2.setSkin('dhx_web');
grid_2.setImagePath('plugins/dhtmlxsuite4/skins/web/imgs/');
//grid_2.setImagePath('plugins/dhtmlxsuite5/skins/terrace/imgs/');
grid_2.setIconset("fontawesome");
grid_2.setHeader(["Name", "Update", "Updated", "Changed", "Inserted"]);
grid_2.setColTypes("tree,ch,icon,icon,icon");
grid_2.setColSorting('str,int,str,str,str');
grid_2.setInitWidthsP('50,*,*,*,*');
grid_2.init();

grid_2.attachEvent('onRowSelect', onGrid2RowSelect);
var size = formLayout.getWidth();

var form_data  = [
    {type:"settings",position:"label-left",offsetLeft:size *0.03,offsetTop:size *0.03},
    {
type: "fieldset",
        label: "Course Details",
        iconset: "awesome",
        list: [
     { type:"settings",position:"label-left",labelWidth:size *0.2,inputWidth: size *0.4,offsetLeft:size *0.01,offsetTop:size *0.01},
    {type:"input",name:"title",label:"Course Name",value: "",
        tooltip:"Title",required:true,
        note:{text:"Course Name."}},
    {type:"input",name:"details",label:"Course Details",value: "",
        value:"", rows:3,
        note:{text:"Course Description."}},
    {type:"input",name:"url",label:"Course URL/link",value: "",
        value:"", rows:3,
        note:{text:"Google document Course link."}},
    {type:"input",name:"local_id",label:"Local Course ID",value: "",
        value:"",
        note:{text:"Local course ID."}},
    {type:"input",name:"emp_id",label:"Employee ID",value: "",
        value:"",
        note:{text:"Who Added the Course."}},
    {type:"input",name:"date_time",label:"Date and Time",value: "",
        value:"",
        note:{text:"When the course was added."}},
            {type:"input",name:"server",label:"Server",value: "",
                value:"",
                note:{text:"Sever to which course was uploaded."}},

    ]
}];



 course_form = formLayout.attachForm(form_data);

// var combo_server = course_form.getCombo("server");

function onToolbar2Click(id) {

    if (id === 'select') {

        grid_2.forEachRow(function (id) {

            var cell = grid_2.cells(id, 1);
            if (cell.isCheckbox())
                cell.setValue(1);
        });
    }

    if (id === 'unselect') {
        grid_2.forEachRow(function (id) {
            var cell = grid_2.cells(id, 1);
            if (cell.isCheckbox())
                cell.setValue(0);
        });
    }

    if (id === 'update') {

        main_layout.progressOn();
        $.get(baseURL + "controller/export_moodle.php?action=6&id=" + doc_id, function (data) {
            main_layout.progressOff();

            if (data.response) {
                updateModules();
            } else {
                dhtmlx.alert({
                    title: 'Warning',
                    text: data.text
                });
            }
        }, 'json');
    }

    if (id === 'delete') {

        if (chapter_id == null) {
            dhtmlx.alert({
                type: "alert-error",
                text: "No Chapter Selected.",
                title: "Error!"
            });
            return;
        }
        main_layout.progressOn();
        $.get(baseURL + "controller/export_moodle.php?action=6&id=" + doc_id, function (data) {
            main_layout.progressOff();

           if (data.response) {
               dhtmlx.message({
                   title: 'success',
                   text: data.text
               });
            } else {
                dhtmlx.alert({
                    title: 'Warning',
                    text: data.text
                });

            }
        }, 'json');
    }


}

function onGrid2RowSelect(id, ind) {

    chapter_id = id;
    tocContentIframe.contentWindow.tinymce.activeEditor.setMode('readwrite');
    tocContentCell.progressOn();
    $.get(baseURL + "controller/documents.php?action=3&id=" + chapter_id , function (data) {
        tocContentCell.progressOff();
        if (data !== null) {
            tocContentIframe.contentWindow.tinymce.activeEditor.setContent(data);
        }
    }, 'json');

    question_grid.updateFromXML(baseURL + 'controller/chapters.php?action=8&id=' + chapter_id, true, true);
    grid.clearAll();
    form_2.setItemValue("answer", "");
    form_2.setItemValue("response", "");
    form_2.setItemValue("score", "");

    form_3.setItemValue("title", "");
    form_3.setItemValue("content", "");

}


function updateModules() {


//get checked rows for new vwersion
    var checked = [];
    grid_2.forEachRow(function (id) {
        if (grid_2.cells(id, 1).getValue() == 1)
            checked.push(id);
    });
    checked = checked.join();
    //get checked rows for archived version
    var archived_checked_arr = [];
    grid_archive.forEachRow(function (id) {
        if (grid_archive.cells(id, 1).getValue() == 1)
            archived_checked_arr.push(id);
    });
    archived_checked_arr = archived_checked_arr.join();

    if (archived_checked_arr == '' && checked == '') {

        dhtmlx.alert({
            title: 'Warning',
            text: 'Check some Page/s or Lesson Page/s to Update'
        });
        return;
    }

    insertDeleteChapters(archived_checked_arr, checked);

}

function insertDeleteChapters(archived_checked_arr, checked) {
    main_layout.progressOn();
    $.get(baseURL + "controller/export_moodle.php?action=14&doc_id=" + doc_id + "&ids=" + checked +"&dids="+ archived_checked_arr, function (data) {
        main_layout.progressOff();

        for (var item in data) {
            if(data[item].response){
                dhtmlx.message({title: 'Success',expire: 6000,text: data[item].text});
            }
            else {
                dhtmlx.alert({title: 'Error',expire: 6000,text: data[item].text});
                return;
            }
        }
        grid_2.updateFromXML(baseURL + 'controller/chapters.php?action=1&id=' + doc_id, true, true);
        grid_archive.updateFromXML(baseURL + 'controller/achived_chapters.php?action=1&id=' + doc_id, true, true);

    }, "json")



}

function insertCurrentVersion(checked, doc_id) {

    main_layout.progressOn();
    $.get(baseURL + "controller/export_moodle.php?action=14&doc_id=" + doc_id + "&id=" + checked, function (data) {

        main_layout.progressOff();
        for (var item in data) {

            if(data[item].response){
                dhtmlx.message({title: 'Success',expire: 3000,text: data[item].text});
            }
            else {
                dhtmlx.message({title: 'Success',expire: 3000,text: data[item].text});
            }
        }
        grid_2.updateFromXML(baseURL + 'controller/chapters.php?action=1&id=' + doc_id, true, true);
        grid_archive.updateFromXML(baseURL + 'controller/achived_chapters.php?action=1&id=' + doc_id, true, true);

    }, "json")

}

function deleteArchive(archived_checked_arr, doc_id) {

    main_layout.progressOn();
    $.get("controller/export_moodle.php?action=13&doc_id=" + doc_id + "&ids=" + archived_checked_arr, function (data) {

        for (var item in data) {
            if(data[item].response){
                dhtmlx.message({title: 'Success',expire: 3000,text: data[item].text});
            }
            else {
                dhtmlx.message({title: 'Success',expire: 3000,text: data[item].text});
            }
        }
        grid_2.updateFromXML('controller/chapters.php?action=1&id=' + doc_id, true, true);
        grid_archive.updateFromXML('controller/achived_chapters.php?action=1&id=' + doc_id, true, true);
    }, "json")


}

