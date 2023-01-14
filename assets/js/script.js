jQuery(document).ready(function ($) {

//  remove the current list item
    function removeLI(e) {
        let parent = this.parentElement;
        let name = parent.querySelector('.user-name').innerHTML;
        let phone = parent.querySelector('.phone-number').innerHTML;


        $.ajax({
            url: MyAjax.ajaxurl,
            type: 'POST',
            data: `action=update_data&name=${name}&phone=${phone}&type=delete`,
            success: function (data) {
                parent.remove();
            }
        });
    }


// Click on a close button to remove item
    let close = document.getElementsByClassName("close");
    for (let i = 0; i < close.length; i++) {
        close[i].addEventListener('click', removeLI);


    }


    let target = document.getElementById('myUL');

    const config = {
        attributes: true,
        childList: true,
        subtree: true,
        attributeFilter: ['class', 'style']
    };

// Note Observer
    const callback = (mutationList, observer) => {
        for (let mutation of mutationList) {
            if (mutation.type === 'childList') {
                let childList = mutation.addedNodes;
                for (const child of childList) {
                    if (child.querySelector('.close')) {
                        child.querySelector('.close').addEventListener('click', removeLI);
                    }
                }
            }
        }
    }

    const observer = new MutationObserver(callback);

    observer.observe(target, config);

    let nameInput = $(this).find('#name');
    let phoneInput = $(this).find('#phone');

// Click on a add button to add new list item
    $('.addBtn').click(function (e) {
        e.preventDefault();

        let nameValue = nameInput.val();
        let phoneValue = phoneInput.val();
        nameInput.removeClass('error');
        phoneInput.removeClass('error');
        if ((nameValue.length <= 2 || (/[0-9]/.test(nameValue) === false)) && (phoneValue === '' || (/[0-9]/.test(phoneValue)) === false || phoneValue.length < 6)) {
            nameInput.addClass('error').focus();
            phoneInput.addClass('error');
        }
        if (nameValue !== '' || phoneValue !== '') {
            $.ajax({
                url: MyAjax.ajaxurl,
                type: 'POST',
                data: `action=update_data&name=${nameValue}&phone=${phoneValue}&type=save`,
                beforeSend: function () {
                    nameInput.val('');
                    phoneInput.val('');
                },
                success: function (data) {
                    if (data !== 'error') {
                        let resPhone = data.phone;
                        let resName = data.name;
                        $('#myUL').append('<li><span class="user-name">' + resName + '</span> <span class="phone-number">' + resPhone + '</span><span class="close">\u00D7</span></li>');
                    } else {
                        alert('number already exist');
                    }
                }
            });
        }
    });


});



