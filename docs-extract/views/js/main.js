window.dhx4.skin = 'dhx_terrace';
var main_layout = new dhtmlXLayoutObject(document.body, '3W');


// var baseURL = 'https://bo.nts.nl/Google_docs_extract/';
var baseURL = '';
var document_ribbon;
var grid_1;
var toolbar_2;
var grid_2;
var grid_archive;
var toolbar_3;
var tocContentIframe;
var statusContentIframe;
var toolbar_4;
var server;
var doc_id = null;
var chapter_id = null;
var question_grid;
var grid;
var form_2
var form_3
var tab_details
var formLayout
var myWidth;
var myHeight;
var course_form;
var doc_name = null;
var doc_url;
var description;
var server_id;
var user_id = null;

/************** Get and set  windows dimensions******************/
if (typeof (main_layout.innerWidth) == 'number') {

    myWidth = window.innerWidth;
    myHeight = window.innerHeight;

} else if (document.documentElement &&
    (document.documentElement.clientWidth || document.documentElement.clientHeight)) {

//IE 6+ in 'standards compliant mode'

    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;

} else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {

//IE 4 compatible

    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;

}

var queryString = window.location.search;
var urlParams = new URLSearchParams(queryString);
user_id = urlParams.get('eid');

/************** courses ******************/
var a = main_layout.cells('a');
a.setText('Courses');
a.setWidth(myWidth * 0.2);


var b = main_layout.cells('b');

b.setWidth(myWidth * 0.37);
var tab_course_datails = b.attachTabbar();
tab_course_datails.addTab('course_datails', 'Course Details');

 tab_details = tab_course_datails.cells('course_datails');
var course_layout = tab_details.attachLayout('1C');

 formLayout =course_layout.cells('a');
formLayout.hideHeader();

tab_course_datails.addTab('chapters', 'Chapters');

var chapter_details = tab_course_datails.cells('chapters');

chapter_details.setActive();


var layout_1 = chapter_details.attachLayout('2E');






/************** chapters ******************/
var cell_1 = layout_1.cells('a');
cell_1.hideHeader();
//cell_1.setWidth(myWidth * 0.8);

var cell_2 = layout_1.cells('b');


/************** archived versions ******************/
cell_2.hideHeader();
//cell_2.setWidth(myWidth * 0.4);;

var c = main_layout.cells('c');
//c.setWidth(myWidth * 0.5);
var tabbar_1 = c.attachTabbar();
tabbar_1.addTab('tab_1', 'Chapter content');
var tab_1 = tabbar_1.cells('tab_1');
tab_1.setActive();

tabbar_1.addTab('tab_5', 'Questions');
var tab_5 = tabbar_1.cells('tab_5');

var tocEditorLayout = tab_1.attachLayout('1C');
var tocContentCell = tocEditorLayout.cells('a');
tocContentCell.hideHeader();

tabbar_1.addTab('tab_2', 'Document Viewer');
var tab_2 = tabbar_1.cells('tab_2');



var questionsEditorLayout = tab_5.attachLayout('2U');

var questionsContentCell = questionsEditorLayout.cells('a');
//questionsContentCell.hideHeader();
questionsContentCell.setText('Questions');

var answerContentCell = questionsEditorLayout.cells('b');
answerContentCell.hideHeader();




tabbar_1.addTab('tab_4', 'Moodle Servers');
var tab_4 = tabbar_1.cells('tab_4');

var serverEditorLayout = tab_4.attachLayout('1C');

var serverContentCell = serverEditorLayout.cells('a');
serverContentCell.hideHeader();



tabbar_1.addTab('tab_3', 'Program Update status');
var tab_3 = tabbar_1.cells('tab_3');

var statusEditorLayout = tab_3.attachLayout('1C');
var statusContentCell = statusEditorLayout.cells('a');
statusContentCell.hideHeader();
tabbar_1.attachEvent("onTabClick", onTabbar1TabClick);



/*************************** main functions ****************************************/

function onTabbar1TabClick(idClicked, idSelected) {

    if (idClicked == 'tab_3') {

        statusContentIframe.contentWindow.tinymce.activeEditor.setContent("");

        $.get(baseURL + "controller/documents.php?action=9", function (data) {
            statusContentIframe.contentWindow.tinymce.activeEditor.setContent(data);
            tab_3.showInnerScroll();
            tab_3.progressOff();

        }, "json");
    }
}

