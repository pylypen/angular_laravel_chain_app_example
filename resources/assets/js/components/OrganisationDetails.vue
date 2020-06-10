<script>

    export default {

        template: require('../views/OrganisationDetails.html'),
        name: "OrganisationDetails",
        mounted() {
            window.axios = require('axios');
            var OrgId = this.$route.query.orgId;
            axios.get('organisations/' + OrgId).then((res) => {
                this.orgDet = res.data.data;
            });

            axios.get('org_users/' + OrgId).then((res) => {
                this.usersOrg = res.data.data;
            });

        },
        methods: {
            change_owner(usersOrg) {
                var OrgId = this.$route.query.orgId;
                if (confirm('Changing the organization owner to new user will also make this user responsible for this organization\'s subscription.')) {
                    axios.post('update_owner/' + OrgId, usersOrg).then((res) => {
                    });
                }
            }
        },

        data() {
            return {
                orgDet: new Array(),
                usersOrg: {},
                is_owner: 1,
                expandNews: false,
                expandSingle: false
            }
        },

    }

</script>


<style>

    .md-table table {
        width: 100% !important;
    }

</style>