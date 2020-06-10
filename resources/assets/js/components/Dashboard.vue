<style>
    .container {
        max-width: 1200px;
    }
</style>
<script>
    export default {
        template: require('../views/Dashboard.html'),
        mounted() {
            window.axios = require('axios');
            axios.get('organisations/all').then((res) => {
                this.orgList = res.data.data;
            });
        },
        data() {
            this.organisationsfetchItems();
            return {
                lists: new Array(),
                pag: {},
                limit: 2,
                addUser: {},
                addUserError: {},
                orgList: {},
                addOrg: {},
                addOrgError: {},
                deleteUserId: 0,
                deleteUserName: '',
                path: 'organisation',
                details_path: 'organisation_details'
            };
        },

        methods: {
            clearModalError() {
                this.addOrgError = {};
                this.addUserError = {};
            },
            createUserAdmin() {
                this.addUserError = {};
                axios.post('users/storeAdmin', this.addUser).then((res) => {
                    jQuery('#newUser .close').click();
                    this.addUser = {};
                    alert('Admin added.');
                    this.usersAdminsfetchItems();
                }).catch(error => {
                    this.addUserError = error.response.data;
                });
            },
            csvUsersExport() {
                axios.get('get_users_csv',{
                    responseType: 'blob',
                }).then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'users_snapshot.csv');
                    document.body.appendChild(link);
                    link.click();
                });
            },

            csvOrgExport(){
                axios.get('get_org_csv',{
                    responseType: 'blob',
                }).then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'organisations_snapshot.csv');
                    document.body.appendChild(link);
                    link.click();
                });
            },

            createUser() {
                this.addUserError = {};
                axios.post('users', this.addUser).then((res) => {
                    jQuery('#newUser .close').click();
                    this.addUser = {};
                    alert('User added.');
                    this.usersfetchItems();
                }).catch(error => {
                    this.addUserError = error.response.data;
                });
            }
            ,
            createOrganisation() {
                this.addOrgError = {};
                axios.post('organisations', this.addOrg).then((res) => {
                    jQuery('#newOrganisation .close').click();
                    this.addOrg = {};
                    alert('Organisation added.');
                    this.organisationsfetchItems();
                }).catch(error => {
                    this.addOrgError = error.response.data;
                });
            }
            ,
            async usersfetchItems(page, searchString) {
                if (typeof page === 'undefined') {
                    page = 1;
                }

                if (typeof searchString === 'undefined') {
                    searchString = '';
                }

                if (searchString.length < 3 && searchString.length !== 0 || searchString.length > 20) return;

                this.$http.get('users?page=' + page + "&searchString=" + searchString)
                    .then(response => {
                        this.path = 'user';
                        jQuery('#organisationsList').addClass('hidden');
                        jQuery('#usersList').removeClass('hidden');
                        this.pag = response.data.data;
                        return response.data.data;
                    }).then(response => {
                    this.lists = response.data;
                })
            }
            ,
            usersAdminsfetchItems(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                this.$http.get('users/admins?page=' + page)
                    .then(response => {
                        this.path = 'cms_admins';
                        jQuery('#organisationsList').addClass('hidden');
                        jQuery('#usersList').removeClass('hidden');
                        this.pag = response.data.data;
                        return response.data.data;
                    }).then(data => {
                    this.lists = data.data;
                })
            }
            ,
            organisationsfetchItems(page, searchString) {
                if (typeof page === 'undefined') {
                    page = 1;
                }

                if (typeof searchString === 'undefined') {
                    searchString = '';
                }

                if (searchString.length < 3 && searchString.length !== 0 || searchString.length > 20) return;

                this.$http.get('organisations?page=' + page + '&searchString=' + searchString)
                    .then(response => {
                        this.path = 'organisation';
                        jQuery('#usersList').addClass('hidden');
                        jQuery('#organisationsList').removeClass('hidden');
                        this.pag = response.data.data;
                        return response.data.data;
                    }).then(data => {
                    this.orgList = data.data;
                })
            }
            ,
            UserGenareteNewPass(id) {
                axios.post('users/confirm_code', {
                    'id': id
                }).then((res) => {
                    alert('New Password sended');
                })
            }
            ,
            deleteUser() {

                axios.delete('users/' + this.deleteUserId).then((res) => {
                    jQuery('#deleteUser .close').click();
                    alert('User deleted.');
                    this.usersfetchItems();
                }).catch(function (error) {
                    if (error.response) {
                        alert(error.response.data.errors.user);
                    } else {
                        alert('Error while deleting user.');
                    }
                });
            }
            ,
            setdeleteUserId(id, name) {
                this.deleteUserId = id;
                this.deleteUserName = name;
            }
        }
    }
</script>