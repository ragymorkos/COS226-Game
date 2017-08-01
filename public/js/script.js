function countDown(secs, elem)
{
	document.getElementById('timeElapsed').value = secs.toString();
	var element = document.getElementById(elem);
	element.innerHTML = secs.toString();
	secs--;
	if(secs < 0)
	{
		clearTimeout(timer);
		document.getElementById('questionform').submit();
		secs = 0;
	}
	var timer = setTimeout('countDown('+secs+',"'+elem+'")', 1000);
}