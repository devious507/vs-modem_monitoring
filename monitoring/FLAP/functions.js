function setDiv(mac,name) {
	document.getElementById('logframe').src = "myFrame.php?mac="+mac+"&name="+name;
	document.getElementById('logfile').style.visibility = "visible";
}
function closeDiv() {
	document.getElementById('logfile').style.visibility = "hidden";
}
function drag_start(event) 
{
	var style = window.getComputedStyle(event.target, null);
	var str = (parseInt(style.getPropertyValue("left")) - event.clientX) + ',' + (parseInt(style.getPropertyValue("top")) - event.clientY)+ ',' + event.target.id;
	event.dataTransfer.setData("Text",str);
} 

function drop(event) 
{
	var offset = event.dataTransfer.getData("Text").split(',');
	var dm = document.getElementById(offset[2]);
	dm.style.left = (event.clientX + parseInt(offset[0],10)) + 'px';
	dm.style.top = (event.clientY + parseInt(offset[1],10)) + 'px';
	event.preventDefault();
	return false;
}

function drag_over(event)
{
	event.preventDefault();
	return false;
}   
