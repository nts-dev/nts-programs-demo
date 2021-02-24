var bl =0;
function author(event_id){    
    var myelement = []; 
      $.ajax({
        async: false,  
        url: "Controller/author.php?action=1",
        type: 'POST',
        data: {"id": event_id,"cnt":bl},
        success: function(data) {
            bl++; 
            myelement[0] = data;          
        }
    });  
    var obj = JSON.parse(myelement[0]);   
    var no_count = obj.count;
    if(no_count == 26){       
        author = function () {};
    }else{         
    }    
   return obj.name; 
  }
  
  