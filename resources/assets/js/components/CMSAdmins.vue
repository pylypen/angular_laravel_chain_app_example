<script>
    export default {
        template: require('../views/CMSAdmins.html'),
        name: "cms_admins",
        data() {
            return {
                dataUser: {},
                UserId: 0,
                dataError: {},
                dataErrorDelete: {}
            };
        },

        mounted() {
            var userId = this.$route.query.userId;
            window.axios = require('axios');
            axios.get('users/' + userId).then((res) => {
                this.dataUser = res.data.data.user;
            })
        },
        methods: {
            updateUser() {
                this.dataErrorDelete = {};
                this.dataError = {};
                var userId = this.$route.query.userId;
                axios.post('users/updateAdmins/' + userId, this.dataUser).then((res) => {
                    alert('Changes saved');
                }).catch(error => {
                    this.dataError = error.response.data;
                });
            }
        }
    }
</script>