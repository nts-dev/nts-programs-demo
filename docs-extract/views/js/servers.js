toolbar_4 = tab_4.attachToolbar();
toolbar_4.setIconset("awesome");
toolbar_4.addButton("add", 0, "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i>&nbsp; Add");
toolbar_4.addSeparator("button_separator_1", 1);
toolbar_4.addButton("delete", 2, "<i class=\"fa fa-trash\" aria-hidden=\"true\"></i> &nbsp; Delete");

server = tab_4.attachGrid();
server.setSkin('dhx_web');
server.setImagePath('plugins/dhtmlxsuite5/skins/web/imgs/');

server.setHeader(["#", "Name", "Domain", "Token", "Path"]);
server.setColTypes("cntr,ro,ed,ed,ed");

server.setColSorting('int,str,str,str,str');
server.setInitWidthsP('5,20,25,35,*');
server.init();
server.load(baseURL + 'controller/chapters.php?action=7');

toolbar_4.attachEvent('onClick', ontoolbar_4Click)

function ontoolbar_4Click (id) {

    if (id === 'add') {

        var selectedId = server.getSelectedRowId();
        var name = '';
        var domain = '';
        var token = '';
        var path = '';
        if (selectedId == null) {
            name = '';
            domain = '';
            token = '';
            path = '';
        } else {
            name = server.cells(selectedId, 1).getValue();
            domain = server.cells(selectedId, 2).getValue();
            token = server.cells(selectedId, 3).getValue();
            path = server.cells(selectedId, 4).getValue();
        }


        addUpdateServer(domain, token, path, name);


    }
    if (id === 'delete') {

        var selectedId = server.getSelectedRowId();
        if (selectedId) {
            $.get(baseURL + "controller/chapters.php?action=13&id=" + selectedId, function (data) {

                tab_4.progressOff();
                dhtmlx.message({
                    title: 'Success',
                    expire: 2000,
                    text: data.text
                });
                server.updateFromXML(baseURL + 'controller/chapters.php?action=7', true, true);

            }, "json");
        }

        if (!selectedId) {

            dhtmlx.alert({
                title: 'Error',
                expire: 2000,
                text: "Select a sever to Delete!"
            });
            return;
        }

    }


}

function addUpdateServer(domain, token, path, name) {

    var windows = new dhtmlXWindows();
    window_8 = windows.createWindow('window_8', myWidth * 0.32, myWidth * 0.0555, myWidth * 0.333, myWidth * 0.24);
    window_8.setText('Add Server');
    window_8.setModal(1);
    window_8.button('park').hide();
    window_8.button('minmax').hide();

    //var size = window_8.getWidth();

    server_form = [
        {
            type: "settings",
            position: "label-left",
            labelWidth: myWidth * 0.09,
            inputWidth: myWidth * 0.18,
            offsetLeft: myWidth * 0.003
        },
        {
            type: "input", name: "name", label: "Name", value: name,
            tooltip: "Server Name", required: true,
            note: {text: "Name."}
        },
        {
            type: "input", name: "domain", label: "Domain", value: domain,
            tooltip: "domain",
            tooltip: "IP", required: true,
            note: {text: "Domain."}
        },
        {
            type: "input", name: "token", label: "Token", value: token, rows: 3,
            tooltip: "token", required: true,
            note: {text: "Token."}
        },
        {
            type: "input", name: "path", label: "Path",value: path,
            tooltip: "path", required: true,
            note: {text: "path."}
        },

        {
            type: "input", name: "location", label: "Location",
            tooltip: "Location",
            note: {text: "location."}
        },

        {
            type: "label", labelWidth: myWidth * 0.26041, list: [
                {
                    type: "label", labelWidth: myWidth * 0.13
                },
                {type: "newcolumn"},
                {type: "button", name: "cancel", value: "Cancel"},
                {type: "newcolumn"},
                {type: "button", name: "save", value: "Save"}
            ]
        },
    ];

    var myForm = window_8.attachForm(server_form);

    myForm.attachEvent("onButtonClick", function (id) {
            if (id == 'cancel') {

                dhtmlx.message({
                    title: 'Success',
                    expire: 5000,
                    text: "Cancelled"
                });
                window_8.close();
            }

            if (id == 'save') {

                var name = myForm.getItemValue('name');
                var domain = myForm.getItemValue('domain');
                var token = myForm.getItemValue('token');
                var path = myForm.getItemValue('path');
                var location = myForm.getItemValue('location');

                if (name == '' || domain == '' || token == ''|| path == '') {

                    dhtmlx.alert({
                        title: 'Warning',
                        text: "Fill All fields marked with a star!"
                    });

                    return;
                }

                let postData = {
                    'name': name,
                    'domain': domain,
                    'token': token,
                    'path': path,
                    'location': location,

                }
                window_8.progressOn();
                $.post(baseURL+ "controller/chapters.php?action=12", postData, function (data) {

                    window_8.progressOff();
                    dhtmlx.message({
                        title: 'Success',
                        expire: 2000,
                        text: data.text
                    });
                    server.updateFromXML(baseURL+'controller/chapters.php?action=7', true, true);
                    window_8.close();
                }, "json");

            }
        }
    );
}
