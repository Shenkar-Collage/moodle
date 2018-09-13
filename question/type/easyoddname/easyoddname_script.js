

M.qtype_easyoddname={}



M.qtype_easyoddname.init_reload = function(Y, url, htmlid){
    var handleSuccess = function(o) {
	        //fischer_template.innerHTML = '';
	//selected = document.getElementById('id_stagoreclip').value;
	//console.log(selected);
        fischer_template.innerHTML = o.responseText;
	M.qtype_easyoddname.insert_structure_into_applet(Y,document.getElementById('id_numofstereo').value);
        //div.innerHTML = "<li>JARL!!!</li>";
    }
    var handleFailure = function(o) {
        /*failure handler code*/
    }
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    }
    var button = Y.one("#id_numofstereo");
    button.on("change", function (e) {   
        div = Y.YUI2.util.Dom.get(htmlid);
        Y.use('yui2-connection', function(Y) {
		newurl = url+document.getElementById('id_numofstereo').value;
		//console.log(newurl);
            Y.YUI2.util.Connect.asyncRequest('GET', newurl, callback);
        });
    });
};




M.qtype_easyoddname.dragndrop = function(Y, url, htmlid){


YUI().use('dd-constrain', 'dd-proxy', 'dd-drop', function(Y) {



    //Listen for all drop:over events
    Y.DD.DDM.on('drop:over', function(e) {
        //Get a reference to our drag and drop nodes
        var drag = e.drag.get('node'),
            drop = e.drop.get('node');
        
	//check to see that we are dropping on list1
	if (drop.get('id') === 'list1' || drop.get('parentNode').get('id') === 'list1') {
		//Are we dropping on a li node?
		if (drop.get('tagName').toLowerCase() === 'li') {
		    //Are we not going up?
		    if (!goingUp) {
		        drop = drop.get('nextSibling');
		    }
		    //Add the node to this list
		    e.drop.get('node').get('parentNode').insertBefore(drag, drop);
		    //Resize this nodes shim, so we can drop on it later.
		    e.drop.sizeShim();
		}
	}


    });
    //Listen for all drag:drag events
    Y.DD.DDM.on('drag:drag', function(e) {
        //Get the last y point
        var y = e.target.lastXY[1];
        //is it greater than the lastY var?
        if (y < lastY) {
            //We are going up
            goingUp = true;
        } else {
            //We are going down.
            goingUp = false;
        }
        //Cache for next check
        lastY = y;
    });
    //Listen for all drag:start events
    Y.DD.DDM.on('drag:start', function(e) {
        //Get our drag object
        var drag = e.target;
        //Set some styles here
        drag.get('node').setStyle('opacity', '.25');

	
//	var nextsib = drag.get('node').get('nextSibling'); 
//	var nsib = drag.get('node').next();
        nextsibling = drag.get('node').next();
//	console.log('nextsibling');
//	console.log(nextsibling);
//	YUI.Env.nextsib = nsib;
	dragparentid = drag.get('node').get('parentNode').get('id');
//	console.log("dragparentid="+dragparentid);
//	console.log(dragparentid); 

//	drag.get('node').insertBefore('<li> here</li>', drag);

        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
        drag.get('dragNode').setStyles({
            opacity: '.5',
            borderColor: drag.get('node').getStyle('borderColor'),
            backgroundColor: drag.get('node').getStyle('backgroundColor')
        }); 
    });
    //Listen for a drag:end events
    Y.DD.DDM.on('drag:end', function(e) {

	//alert('drag:end');

        var drag = e.target;
        //Put our styles back  crl-this is style of dragged node
        drag.get('node').setStyles({
            visibility: '',
            opacity: '1'
        }); 
    });


    Y.DD.DDM.on('drop:hit', function(e) {
	var drop = e.drop.get('node'),
            drag = e.drag.get('node');

//	console.log("drop id="+drop.get('id'));
//	console.log("drag id="+drag.get('parentNode').get('id'));
//	console.log("drag parentid ="+YUI.Env.dragparentid);
        var flag = false;
	if(drop.get('tagName').toLowerCase() === "li" && drop.get('id') === 'list1'){
	flag = true;
	}	

//		console.log("drop.getid="+drop.get('id'));

		if(dragparentid !== "list1" && (drop.get('id') === "list1" || drop.get('tagName').toLowerCase() === 'li')){
			//var newnode = drag.get('parentNode').insertBefore('<li class="list2">'+drag.get('innerHTML')+'</li>', YUI.Env.nextsib);
//			console.log("Should be inserted");

			if(nextsibling !== null){
			var newnode = drag.get('parentNode').insertBefore('<li class="list2">'+drag.get('innerHTML')+'</li>', nextsibling);
			}
			else{
//			console.log("nextsibling must be null");
			var newnode = drag.get('parentNode').insertBefore('<li class="list2">'+drag.get('innerHTML')+'</li>', Y.one('#'+dragparentid).get('lastChild'));
			}

//insert('&nbsp; <strong>before last child</strong> &nbsp;', node.get('lastChild'));

			dd = new Y.DD.Drag({
			    node: newnode,
			    target: {
				padding: '0 0 0 20'
			    }
			}).plug(Y.Plugin.DDProxy, {
			    moveOnEnd: false,
			  //  hideOnEnd: false,
			  //  cloneNode: true
			}).plug(Y.Plugin.DDConstrained, {
			    constrain2node: '#play'
			});
		}
    });



    //Listen for all drag:drophit events
    Y.DD.DDM.on('drag:drophit', function(e) {

//	alert('drophit');

        var drop = e.drop.get('node'),
            drag = e.drag.get('node');
//	    dragnode = e.drag.get('dragNode');
//	console.log('here');
//	console.log('here2');
//	console.log(drop);
//	console.log(drag);
//	console.log(dragnode);
//	alert(drop);


//       console.log(Y.all('#list1 li:not(.empty)').size());
///crl
//        if(Y.all('#list1 li:not(.empty)').size() <= 0){
	//    Y.one('.empty').setStyle('display', 'null');
/*	var empty=Y.one('.empty');
	if(empty){
	    Y.one('.empty').remove();
 	}  */
//            Y.one('#list1 .empty').setStyle('display',null); 
//	console.log('in here');
//        }

//	drag.appendChild(drag);
//	drop.get('parentNode').insertBefore(drop, drag);
        //if we are not on an li, we must have been dropped on a ul
        if (drop.get('tagName').toLowerCase() !== 'li') {
            if (!drop.contains(drag)) {
		//console.log('must be ul');
                drop.appendChild(drag);
            }
        }
    });
    
    //Static Vars
    var goingUp = false, lastY = 0;
    var nextsibling = '';
    var dragparentid = '';

    //Get the list of li's in the lists and make them draggable


    var lis = Y.Node.all('#play ul li');
    lis.each(function(v, k) {
        var dd = new Y.DD.Drag({
            node: v,
            target: {
                padding: '0 0 0 20'
            }
        }).plug(Y.Plugin.DDProxy, {
            moveOnEnd: false,
	  //  hideOnEnd: false,
          //  cloneNode: true
        }).plug(Y.Plugin.DDConstrained, {
            constrain2node: '#play'
        });
    }); 


    //Create simple targets for the 2 lists.
//    var uls = Y.Node.all('#play ul');
    var uls = Y.Node.all('#answerdiv , #list1 , #trashcan');
    uls.each(function(v, k) {
	//console.log(v+' and '+k);
        var tar = new Y.DD.Drop({
            node: v
        });
    });
    
});


};




M.qtype_easyoddname.init_getanswerstring = function(Y, moodle_version){
    var handleSuccess = function(o) {

    };
    var handleFailure = function(o) {
        /*failure handler code*/
    };
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    };
    if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
        YAHOO = Y.YUI2;
    }


    Y.all(".id_insert").each(function(node) {
    	node.on("click", function () {

	//        alert(node.getAttribute("id"));

		var items = document.getElementById('list1').childNodes;
	            var out = ""; 
	            for (i=1;i<items.length;i=i+1) {
			if (i == items.length - 1){
	                out += items[i].innerHTML;
			}
			else{
			out += items[i].innerHTML;
			}
	            } 
	
        var buttonid = node.getAttribute("id");
	textfieldid = 'id_answer_' + buttonid.substr(buttonid.length - 1);
	document.getElementById(textfieldid).value = out;

	//		alert(out);
    	});
    });
};










