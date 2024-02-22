var addBtns = document.querySelectorAll('#settings-tab-custom-fields .btn.add');
addBtns.forEach(function(addBtn){
    addBtn.addEventListener('click', handleNewRow);
});

function disableAllTextarea() {
    var textareas = document.querySelectorAll('.custom-value-column textarea');
    textareas.forEach(function(textarea){
        if(textarea.parentElement.parentElement.querySelector('.type-column select').value !== 'custom'){
            textarea.disabled = "disabled";

        }
    });
}
disableAllTextarea();

function addEventSelects() {
    var selects = document.querySelectorAll('.type-column select');
    selects.forEach(function(select){
        select.addEventListener('change', handleCustomValue);
    });
}
addEventSelects();

function removeEventSelects() {
    var selects = document.querySelectorAll('.type-column select');
    selects.forEach(function(select){
        select.removeEventListener('change', handleCustomValue);
    });
}

function handleCustomValue(e) {
    if(e.currentTarget.value === 'custom') {
        e.currentTarget.parentElement.parentElement.parentElement.querySelector('.custom-value-column textarea').removeAttribute("disabled");
    } else {
        e.currentTarget.parentElement.parentElement.parentElement.querySelector('.custom-value-column textarea').disabled = "disabled";
        e.currentTarget.parentElement.parentElement.parentElement.querySelector('.custom-value-column textarea').value = '';
    }
}

function handleNewRow(e) {
    setTimeout(function () {
        disableAllTextarea();
        removeEventSelects();
        addEventSelects();
    }, 0);
    
}
