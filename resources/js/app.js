document.querySelectorAll('[data-repeater]').forEach((repeater) => {
	const list = repeater.querySelector('[data-repeater-list]');
	const template = repeater.querySelector('template');
	let nextIndex = Number(repeater.dataset.nextIndex || 0);

	repeater.querySelector('[data-add-row]')?.addEventListener('click', () => {
		const row = document.createElement('div');
		row.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
		list.append(row.firstElementChild);
	});

	repeater.addEventListener('click', (event) => {
		if (event.target.closest('[data-remove-row]')) {
			event.target.closest('.repeater-row').remove();
		}
	});
});

document.querySelectorAll('[data-avatar-input]').forEach((input) => {
	input.addEventListener('change', () => {
		const file = input.files?.[0];
		if (!file) return;

		const image = input.closest('.avatar-upload').querySelector('[data-avatar-preview]');
		const preview = image.querySelector('img') || document.createElement('img');
		preview.src = URL.createObjectURL(file);
		preview.alt = 'Selected profile photo';
		image.replaceChildren(preview);
	});
});

document.querySelectorAll('[data-copy-url]').forEach((button) => {
	button.addEventListener('click', async () => {
		await navigator.clipboard.writeText(button.dataset.copyUrl);
		button.textContent = 'Link copied';
		window.setTimeout(() => { button.textContent = 'Copy link'; }, 1800);
	});
});
