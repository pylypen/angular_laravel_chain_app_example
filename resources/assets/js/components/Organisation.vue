<script>
    export default {
        template: require('../views/Organisation.html'),
        name: "organisation",
        data() {
            return {
                dataOrg: {},
                dataOrgError: {},
                OrgId: 0
            };
        },

        mounted() {
            var OrgId = this.$route.query.orgId;
            window.axios = require('axios');
            axios.get('organisations/' + OrgId).then((res) => {
                this.dataOrg = res.data.data;
            })
        },
        methods: {
            saveOrganisation() {
                var logo = jQuery('#logo');
                var cover_picture = jQuery('#cover_picture');
                var formData = new FormData();
                this.dataOrgError = {};
                var OrgId = this.$route.query.orgId;

                var file = logo[0].files;

                if (file != undefined && file.length) {
                    formData.append('_logo', file[0]);
                }

                var file = cover_picture[0].files;

                if (file != undefined && file.length) {
                    formData.append('_cover_picture', file[0]);
                }
                formData.append('email', this.dataOrg.email);
                formData.append('name', this.dataOrg.name);
                formData.append('phone_number', this.dataOrg.phone_number);
                formData.append('state', this.dataOrg.state);
                formData.append('city', this.dataOrg.city);
                formData.append('street', this.dataOrg.street);
                formData.append('zip', this.dataOrg.zip);

                axios.post('organisations/update/' + OrgId, formData).then((res) => {
                    alert('Changes saved');
                    location.reload();
                }).catch(error => {
                    this.dataOrgError = error.response.data;
                });

                return false;
            }
        }

    }
</script>