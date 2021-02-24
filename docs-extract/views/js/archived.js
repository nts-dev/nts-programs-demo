toolbar_3 = cell_2.attachToolbar();
toolbar_3.setIconset("awesome");
toolbar_3.addButton("select", 0, "<i class=\"fa fa-check-square-o\" aria-hidden=\"true\"></i>&nbsp; Select All");
toolbar_3.addSeparator("button_separator_1", 1);
toolbar_3.addButton("unselect", 2, "<i class=\"fa fa-square-o\" aria-hidden=\"true\"></i>&nbsp;Unselect All");
toolbar_3.addSeparator("button_separator_2", 3);
toolbar_3.addButton("restore", 4, "<i class='fa fa-undo fa-3x' aria-hidden='true'></i>&nbsp; Restore");
toolbar_3.addSeparator("button_separator_3", 5);
toolbar_3.addButton("delete", 6, "<i class=\"fa fa-trash\" aria-hidden=\"true\"></i> &nbsp; Delete Version");
toolbar_3.attachEvent('onClick', onToolbar3Click);

grid_archive = cell_2.attachGrid();
grid_archive.setSkin('dhx_web');
grid_archive.setImagePath('plugins/dhtmlxsuite4/skins/web/imgs/');
grid_archive.setHeader(["Name", "Restore", "Updated", "Changed", "Deleted"]);
grid_archive.setColTypes("tree,ch,icon,icon,icon");
grid_archive.setColSorting('str,int,str,str,str');
grid_archive.setInitWidthsP('50,*,*,*,*');
grid_archive.init();

grid_archive.attachEvent('onRowSelect', onGridArchiveRowSelect);

function onGridArchiveRowSelect(id, ind) {

    tocContentIframe.contentWindow.tinymce.activeEditor.setMode('readonly');
    tocContentCell.progressOn();
    $.get(baseURL + "controller/documents.php?action=10&id=" + id, function (data) {
        tocContentCell.progressOff();
        if (data !== null) {
            tocContentIframe.contentWindow.tinymce.activeEditor.setContent(data);

        }
    }, 'json');

}

function onToolbar3Click(id) {

    if (id === 'select') {
        grid_archive.forEachRow(function (id) {
            var cell = grid_archive.cells(id, 1);
            if (cell.isCheckbox())
                cell.setValue(1);
        });
    }
    if (id === 'unselect') {
        grid_archive.forEachRow(function (id) {
            var cell = grid_archive.cells(id, 1);
            if (cell.isCheckbox())
                cell.setValue(0);
        });

    }
    if (id === 'restore') {

        dhtmlx.alert({
            title: 'Warning',
            text: "To be implemented!"
        });


    }

    if (id === 'delete') {

        dhtmlx.confirm({
            title: "Delete Archived Version",
            type: "confirm-warning",

            text: "Are you sure you want to delete This Archived Version?",
            callback: function (ok) {

                if (ok) {

                    $.get(baseURL + "controller/achived_chapters.php?action=6&id=" + docID, function (data) {
                        main_layout.progressOff();


                        if (!data.response) {
                            dhtmlx.alert({
                                title: 'Warning',
                                text: data.text
                            });
                            return;
                        }
                        grid_archive.clearAll();
                        dhtmlx.message({
                            title: 'Warning',
                            text: data.text
                        });

                    }, 'json');

                } else {
                    dhtmlx.message({
                        title: 'Success',
                        expire: 2000,
                        text: "Process cancelled"
                    });
                }


            }
        });


    }


}



function deleteArchive(archived_checked_arr, doc_id) {

    main_layout.progressOn();
    $.get(baseURL + "controller/export_moodle.php?action=13&doc_id=" + doc_id + "&ids=" + archived_checked_arr, function (data) {

        main_layout.progressOff();
        dhtmlx.message({
            title: 'Success',
            expire: 3000,
            text: data.text
        })
        grid_2.updateFromXML(baseURL + 'controller/chapters.php?action=1&id=' + docID, true, true);
        archive.updateFromXML(baseURL + 'controller/achived_chapters.php?action=1&id=' + docID, true, true);
    }, "json")


}