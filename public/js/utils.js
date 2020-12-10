$(document).ready(function(){

    $('#userList').bootstrapTable({
        url: $('#url').val(),
        pagination: true,
        search: true,
        pageSize : 5,
        pageList : [5, 10, 25, 50, 100],
        showFooter : false,
        btSelectItem : 'id',
        columns: [{
            field: 'id',
            title: 'id',
            sortable : true,
        },{
            field: 'firstName',
            title: 'First name',
            sortable : true
        }, {
            field: 'lastName',
            title: 'Last name',
            sortable : true
        }, {
            field: 'email',
            title: 'Email'
        }, {
            field: 'organization',
            title: 'Organisation',
            sortable : true
        }, {
            field: 'balance',
            title: 'Balance'
        }, {
            field: 'actions',
            title: 'Actions'
        }]
    });

});