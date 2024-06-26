var btn = document.querySelector('#settings-clean_users');
btn.addEventListener("click", updateValue);

function updateValue() {
    var input = document.querySelector('input[name="settings[clean_users]"]');
    var container = document.querySelector('#settings-subfields');

    if (input.value) {
        container.style.maxHeight = container.scrollHeight + "px";
        setTimeout(() => {
            container.style.overflow = 'visible';
            container.style.maxHeight = 'unset';
        }, 400);
    } else {
        container.style.overflow = '';
        container.style.maxHeight = null;
    }
}
updateValue();