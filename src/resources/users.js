var btn = document.querySelector('#settings-clean_users');
btn.addEventListener("click", updateValue);

function updateValue() {
    var input = document.querySelector('input[name="settings[clean_users]"]');
    var container = document.querySelector('#settings-subfields');

    if (input.value) {
        container.style.maxHeight = container.scrollHeight + "px";
    } else {
        container.style.maxHeight = null;
    }
}
updateValue();