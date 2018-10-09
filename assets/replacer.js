
let images = document.getElementsByTagName('img');

for( let image of images ) {

	let containerWidth = image.clientWidth
	let optWidth = image.dataset.optWidth
	let optHeight = image.dataset.optHeight

	let containerHeight = parseInt( optHeight/optWidth * containerWidth )

	image.style.width = containerWidth + "px";
	image.style.height = containerHeight + "px";

	let optSrc = image.dataset.optSrc

	console.log( optSrc );

	let downloadingImage = new Image();

	downloadingImage.onload = function(){
		if ( this.complete ) {
			image.src = this.src;
		}
	};
	downloadingImage.src = optSrc;
}