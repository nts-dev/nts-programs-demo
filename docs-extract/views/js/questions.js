toolbar_5 = tab_5.attachToolbar();
toolbar_5.addButton("save", 0, "<i class=\"fa fa-floppy-o\" aria-hidden=\"true\"></i> &nbsp; Save");
toolbar_5.addSeparator("button_separator_3", 1);
toolbar_5.addButton("delete", 2, "<i class=\"fa fa-trash\" aria-hidden=\"true\"></i> &nbsp; Delete");


var answersEditorLayout = answerContentCell.attachLayout('3E');

var questionDetails = answersEditorLayout.cells('a');
questionDetails.setText('Question Details');
questionDetails.setHeight(myHeight* 0.25);

var answer_grid = answersEditorLayout.cells('b');
answer_grid.setText('Answers');

grid = answer_grid.attachGrid();
grid.setSkin('dhx_web');
grid.setImagePath('plugins/dhtmlxsuite4/skins/web/imgs/');
//grid.setImagePath('plugins/dhtmlxsuite5/skins/terrace/imgs/');
grid.setHeader(["#", "Answer"]);
grid.setColTypes("cntr,ro,ed");
grid.setColSorting('int,str');
grid.setInitWidths('50,*');
grid.init();


var answer_details= answersEditorLayout.cells('c');
answer_details.setText('Answers Details');
answer_details.setHeight(myHeight* 0.35);
var size = questionDetails.getWidth();

 var question_form  = [
     {type:"settings",position:"label-left",labelWidth:size *0.2,inputWidth: size *0.6,offsetLeft:size *0.08},
    {type:"input",name:"title",label:"Title",value:"",
        tooltip:"Title",required:true,
        note:{text:"Question title."}},
    {type:"input",name:"content",label:"Content",
        value:"", rows:3,
        note:{text:"Question Description."}},
     {type: "select",name:"selections", label: "Type", options:[
             {value: "10", text: "Essay"},
             {value: "3", text: "Multichoice"},
             {value: "5", text: "Matching"},
             {value: "8", text: "Numerical"},
             {value: "1", text: "Short Answer"},
             {value: "2", text: "True/False"}
         ]}
];

answer_form = [
    {type:"settings",position:"label-top",labelWidth:size *0.3,inputWidth: size *0.8,offsetLeft:size *0.1},
    {type:"input",name:"answer",label:"Answer",value:"", rows:3,
        tooltip:"Title",required:true,
        note:{text:"Answer Details."}},
    {type:"input",name:"response",label:"Response",
        value:"",tooltip:"Response",required:true,
     },
    {type:"input",name:"score",label:"Score",
        value:"",tooltip:"Score",required:true,
    },
    {type: "select", name:"selection",label: "Jumpto", options:[
            {value: "-1", text: "Next Page"},
            {value: "0", text: "This Page"},

        ]}
];


form_2 = answer_details.attachForm(answer_form);




form_3 = questionDetails.attachForm(question_form);

question_grid = questionsContentCell.attachGrid();

question_grid.setImagePath('plugins/dhtmlxsuite5/skins/terrace/imgs/');
question_grid.setHeader(["#", "Title", "Type"]);
question_grid.setColTypes("cntr,ro,ed,ed");
question_grid.setColSorting('int,str,str');
question_grid.setInitWidths('50,*,*');
question_grid.init();

question_grid.attachEvent('onRowSelect', onquestion_gridRowSelect);



grid.attachEvent('onRowSelect', ongridRowSelect);

function ongridRowSelect(id, ind) {



    $.get(baseURL + "controller/chapters.php?action=10&id=" + id, function (data) {

        form_2.setItemValue("answer", data.answer);
        form_2.setItemValue("response", data.response);
        form_2.setItemValue("score", data.score);
        form_2.setItemValue("selection", data.jumpto);
    }, "json");

}


function onquestion_gridRowSelect(id, ind) {


    $.get(baseURL + "controller/chapters.php?action=11&id=" + id, function (data) {
        form_3.setItemValue("title", data.title);
        form_3.setItemValue("content", data.text);
        form_3.setItemValue("selections", data.type);

    }, "json");
    addAnswers(id);
}
function addAnswers(id) {
    grid.updateFromXML(baseURL + 'controller/chapters.php?action=9&id=' + id, true, true);
    form_2.setItemValue("answer", "");
    form_2.setItemValue("response", "");
    form_2.setItemValue("score", "");
}