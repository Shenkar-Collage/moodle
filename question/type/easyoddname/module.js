M.qtype_easyoddname={
    insert_easyoddname_applet : function(Y, topnode, feedback, readonly, stripped_answer_id, slot){
 
	    var inputdiv = Y.one(topnode);
	    inputdiv.ancestor('form').on('submit', function (){

		var items = document.getElementById('list1'+slot).childNodes;
	            var out = ""; 
	            for (i=0;i<items.length;i=i+1) {
			if (i == items.length - 1){
	                out += items[i].innerHTML;
			}
			else{
			out += items[i].innerHTML;
			}
	            } 
		Y.one(topnode+' input.answer').set('value', out);
            }, this);
    },
}


M.qtype_easyoddname.dragndrop = function(Y, slot){


YUI().use('dd-constrain', 'dd-proxy', 'dd-drop', function(Y) {



    //Listen for all drop:over events
    Y.DD.DDM.on('drop:over', function(e) {
        //Get a reference to our drag and drop nodes
        var drag = e.drag.get('node'),
            drop = e.drop.get('node');
        
	//check to see that we are dropping on list1
	if (drop.get('id') === 'list1'+slot || drop.get('parentNode').get('id') === 'list1'+slot) {
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

        nextsibling = drag.get('node').next();
	dragparentid = drag.get('node').get('parentNode').get('id');
        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
        drag.get('dragNode').setStyles({
            opacity: '.5',
            borderColor: drag.get('node').getStyle('borderColor'),
            backgroundColor: drag.get('node').getStyle('backgroundColor')
        }); 
    });
    //Listen for a drag:end events
    Y.DD.DDM.on('drag:end', function(e) {

        var drag = e.target;
        drag.get('node').setStyles({
            visibility: '',
            opacity: '1'
        }); 
    });


    Y.DD.DDM.on('drop:hit', function(e) {
	var drop = e.drop.get('node'),
            drag = e.drag.get('node');
        var flag = false;
	if(drop.get('tagName').toLowerCase() === "li" && drop.get('id') === 'list1'+slot){
	flag = true;
	}	

		if(dragparentid !== "list1"+slot && (drop.get('id') === "list1"+slot || drop.get('tagName').toLowerCase() === 'li')){

			if(nextsibling !== null){
			var newnode = drag.get('parentNode').insertBefore('<li class="list2">'+drag.get('innerHTML')+'</li>', nextsibling);
			}
			else{
			var newnode = drag.get('parentNode').insertBefore('<li class="list2">'+drag.get('innerHTML')+'</li>', Y.one('#'+dragparentid).get('lastChild'));
			}

			dd = new Y.DD.Drag({
			    node: newnode,
			    target: {
				padding: '0 0 0 20'
			    }
			}).plug(Y.Plugin.DDProxy, {
			    moveOnEnd: false,
			}).plug(Y.Plugin.DDConstrained, {
			    constrain2node: '#play'+slot
			});
		}
    });



    //Listen for all drag:drophit events
    Y.DD.DDM.on('drag:drophit', function(e) {

        var drop = e.drop.get('node'),
            drag = e.drag.get('node');
        if (drop.get('tagName').toLowerCase() !== 'li') {
            if (!drop.contains(drag)) {
                drop.appendChild(drag);
            }
        }
    });
    
    //Static Vars
    var goingUp = false, lastY = 0;
    var nextsibling = '';
    var dragparentid = '';

    //Get the list of li's in the lists and make them draggable


    var lis = Y.Node.all('.list2');
    lis.each(function(v, k) {
        var dd = new Y.DD.Drag({
            node: v,
            target: {
                padding: '0 0 0 20'
            }
        }).plug(Y.Plugin.DDProxy, {
            moveOnEnd: false,
        }).plug(Y.Plugin.DDConstrained, {
        });
    }); 


    var uls = Y.Node.all('.dropable');
    uls.each(function(v, k) {
	//console.log(v+' and '+k);
        var tar = new Y.DD.Drop({
            node: v
        });
    });
    
});


};

