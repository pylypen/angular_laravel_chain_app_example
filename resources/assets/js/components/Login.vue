<script>
    export default {
        template: require('../views/Login.html'),
        data() {
            return {
                email: null,
                password: null,
                error: false
            }
        },
        methods: {
            login() {
                this.error = false;
                var app = this
                this.$auth.login({
                    params: {
                        email: app.email,
                        password: app.password
                    },
                    success: function () {
                    },
                    error: function () {
                        this.error = true;
                    },
                    rememberMe: true,
                    redirect: '/dashboard',
                    //fetchUser: true
                });
            },
        }
    }
</script>