if (location.hostname=='football2.myfantasyleague.com' && location.pathname.indexOf('/2011/home/49522') >= 0){
    window.location.replace(window.location.href.replace('football2', 'www2'));
}

if (location.pathname=='/2011/submit_lineups'){
	newI=document.createElement('iframe');
	newI.setAttribute('id', 'putzIFrame');
	newI.setAttribute('src', "http://mfl.f-con.us/flexsetup.php?true_id="+franchise_id);
	newI.setAttribute('scrolling', 'no');
	newI.setAttribute('marginwidth', '0');
	newI.setAttribute('marginheight', '0');
	newI.setAttribute('frameborder', '0');
	newI.setAttribute('style', 'padding:0px;margin:0px;border:0px;width:80%;height:550px;');
	newI.style.width='80%';
	newI.style.height='540px';
	document.getElementById('contentframe').insertBefore(newI, document.getElementById('contentframe').getElementsByTagName('table')[0]);
}
/*for (i=0;i<document.getElementsByTagName('TD').length;i++){
	if (document.getElementsByTagName('TD')[i].innerHTML.indexOf("Free Agent</span> - <")!=-1
			&& document.getElementsByTagName('TD')[i].getElementsByTagName('TD').length==0){
		newa=document.createElement('a');
		newa.href=document.getElementsByTagName('TD')[i].lastChild.href.replace('O=130', 'O=46');
		newt=document.createTextNode("Claim");
		news=document.createTextNode(" - ");
		newa.appendChild(newt);
		document.getElementsByTagName('TD')[i].appendChild(news);
//		document.getElementsByTagName('TD')[i].removeChild(document.getElementsByTagName('TD')[i].childNodes[1]);
		document.getElementsByTagName('TD')[i].appendChild(newa);
	}
} */

