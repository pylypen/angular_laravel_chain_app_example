<script>
import {format} from 'date-fns'

    export default {
        template: require('../views/User.html'),
        name: "user",
        data() {
            return {
                dataUser: {},
                trial_ends_at: '',
                dataOrg: {},
                UserOrganisationid: 0,
                UserId: 0,
                current_time: new Date().getTime() / 1000,
                trial_status: false,
                dataError: {},
                dataErrorDelete: {}
            };
        },

        mounted() {
            var userId = this.$route.query.userId;
            let dateFormat = this.$material.locale.dateFormat || 'yyyy-MM-dd';

            window.axios = require('axios');
            axios.get('users/' + userId).then((res) => {
                this.dataUser = res.data.data.user;
                this.dataOrg = res.data.data.orgs;
                let trial_ends_seconds = new Date(this.dataUser.trial_ends_at).getTime() / 1000;

                console.log(trial_ends_seconds);
                console.log(this.current_time);
                if (this.dataUser.trial_ends_at != null && trial_ends_seconds > this.current_time) {
                    this.trial_ends_at = format(new Date(this.dataUser.trial_ends_at), dateFormat);

                    this.trial_status = true;
                }
            });
        },


        methods: {
            setUser(id_org_user, org_id, type, self) {
                var userId = this.$route.query.userId;
                axios.post('users/set_organisation_setting',
                    {
                        'id_org_user': id_org_user,
                        'user_id': userId,
                        'type': type,
                        'set': self,
                        'org_id': org_id
                    }
                ).catch(error => {
                    alert(error.response.data.errors.organization);


                    for (var i in this.dataOrg) {
                        if (this.dataOrg[i].id == id_org_user) {
                            this.dataOrg[i][type] = !this.dataOrg[i][type];
                        }
                    }

                });
            },
            deleteUserOrg(UserOrganisationid, Userid) {
                this.dataErrorDelete = {};
                axios.post('users/delete_user_organisation',
                    {
                        'id_org_user': UserOrganisationid,
                        'user_id': Userid
                    }
                ).then((res) => {
                    window.location.reload();
                }).catch(error => {
                    this.dataErrorDelete = error.response.data.errors.user_organisation;
                });
            },
            updateUser() {
                this.dataErrorDelete = {};
                this.dataError = {};

                let dateTime = new Date().toJSON().slice(0,10);
                var avatar = jQuery('#avatar');
                var formData = new FormData();
                var file = avatar[0].files;
                var userId = this.$route.query.userId;

                if (file != undefined && file.length) {
                    formData.append('avatar', file[0]);
                }
                if (this.dataUser.phone_number != undefined && this.dataUser.phone_number.length) {
                    formData.append('phone_number', this.dataUser.phone_number);
                }

                formData.append('nickname', this.dataUser.nickname);
                formData.append('first_name', this.dataUser.first_name);
                formData.append('last_name', this.dataUser.last_name);
                formData.append('contact_email', this.dataUser.contact_email);
                formData.append('trial_status', this.trial_status);

                if (!this.trial_status) {
                    formData.append('trial_ends_at', dateTime);
                } else {
                    formData.append('trial_ends_at', this.trial_ends_at);
                }


                axios.post('users/update/' + userId, formData).then((res) => {
                    alert('Changes saved');

                    location.reload();
                }).catch(error => {
                    this.dataError = error.response.data;
                });
            }
        }
    }
</script>