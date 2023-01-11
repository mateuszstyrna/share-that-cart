(function () {
	function copy() {
		const input = document.getElementById('sc__link');
		input.focus();
		input.select();
		document.execCommand('copy');
	}

	const callback = function(mutationsList, observer) {
		const popup = document.querySelector('#sc__popup');
		const button = document.getElementById('sc__button');
		button.addEventListener('click', (e)=>{
			e.preventDefault();
			copy();
			popup.style.display = "block";
		});

		popup.addEventListener('click', ()=>{
			popup.style.display = "none";
		});
	};

	if (document.getElementById('sc__button')){
		const popup = document.querySelector('#sc__popup');
		const button = document.getElementById('sc__button');
		button.addEventListener('click', (e)=>{
			e.preventDefault();
			copy();
			popup.style.display = "block";
		});

		popup.addEventListener('click', ()=>{
			popup.style.display = "none";
		});

		const target_block = document.querySelector('.entry-content');
		const config = { attributes: true, childList: true, subtree: true };
		const observer = new MutationObserver(callback);
		observer.observe(target_block, config);
	}
})();