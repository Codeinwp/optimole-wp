
let images = document.getElementsByTagName('img');

for( let image of images ) {

	let containerWidth = image.clientWidth
	let optWidth = image.attributes.width.value
	let optHeight = image.attributes.height.value

	let containerHeight = parseInt( optHeight/optWidth * containerWidth )

	image.style.width = containerWidth + "px";
	image.style.height = containerHeight + "px";

	let optSrc = image.dataset.optSrc

	console.log( containerWidth );
	console.log( containerHeight );
	console.log( optWidth );
	console.log( optHeight );

	let downloadingImage = new Image();

	downloadingImage.onload = async function(){
		if ( this.complete ) {
			image.classList.add('optml_lazyload_img');
			image.src = this.src;
		}
	};
	optSrc = optSrc.replace(`/${optWidth}/`, `/${containerWidth}/`)
	optSrc = optSrc.replace(`/${optHeight}/`, `/${containerHeight}/`)
	console.log( optSrc );
	downloadingImage.src = optSrc;
}