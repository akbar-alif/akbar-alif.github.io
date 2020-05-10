<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12 mt-3">
                <div class="card card-widget widget-user">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header text-white" style="background-image:url('./img/user-cover-dark-red.png')">
                        <h3 class="widget-user-username">{{ this.form.name }}</h3>
                        <h5 class="widget-user-desc">{{ this.form.type }}</h5>
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle" :src="getProfilePhoto()" alt="User Avatar">
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">3,200</h5>
                                    <span class="description-text">SALES</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">13,000</h5>
                                    <span class="description-text">FOLLOWERS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4">
                                <div class="description-block">
                                    <h5 class="description-header">35</h5>
                                    <span class="description-text">PRODUCTS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
                            <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="settings">
                                <form class="form-horizontal">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                                        <div class="col-sm-10">
                                            <input type="email" v-model="form.name" class="form-control" id="inputName"
                                                   placeholder="Name" :class="{ 'is-invalid': form.errors.has('name') }">
                                            <has-error :form="form" field="name"></has-error>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" v-model="form.email" class="form-control" id="inputEmail"
                                                   placeholder="Email" :class="{ 'is-invalid': form.errors.has('email') }">
                                            <has-error :form="form" field="email"></has-error>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputExperience" class="col-sm-2 col-form-label">Experience</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" v-model="form.bio" id="inputExperience" placeholder="Experience"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="profilePhoto" class="col-sm-2 col-form-label">Profile Photo</label>
                                        <div class="col-sm-10">
                                            <input type="file" @change="updateProfilePhoto" id="profilePhoto" name="profilePhoto">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="password" class="col-sm-2 col-form-label">Password (leave empty if not changing)</label>
                                        <div class="col-sm-10">
                                            <input type="password" v-model="form.password" class="form-control" id="password"
                                                   placeholder="Password" :class="{ 'is-invalid': form.errors.has('password') }">
                                            <has-error :form="form" field="password"></has-error>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <button type="submit" @click.prevent="updateProfile" class="btn btn-success">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- /.tab-pane -->

                            <div class="tab-pane" id="activity">
                                <h4>User activity</h4>
                            </div>
                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
        </div>

    </div>
</template>

<style>
    .widget-user-header {
        background-position: center center;
        height: 200px !important;
    }

    .card-footer {
        padding: 0px !important;
    }

</style>
<script>
    export default {
        data() {
            return {
                form: new Form({
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: ''
                }),
            }
        },

        created() {
            this.load();
        },

        methods: {
            load() {
                axios.get("api/profile")
                    .then(response => this.form.fill(response.data))
                    .catch(error => console.log(error));
            },

            getProfilePhoto() {
                return this.form.photo.length > 200 ? this.form.photo : "img/profile/" + this.form.photo;
            },

            updateProfile() {
                this.$Progress.start();

                if(this.form.password && this.form.password.length === 0)
                    this.form.password = undefined;

                this.form.put("api/profile")
                    .then(() => {
                        toast.fire({
                            type: "success",
                            text: "Profile updated successfully!"
                        });

                        this.load();
                        this.$Progress.finish();
                    }).catch((error) => {
                        console.log(error);
                        this.$Progress.fail();
                });
            },

            updateProfilePhoto(event) {
                let file = event.target.files[0];

                if(file["size"] < 2111775) { // < 2mb
                    let reader = new FileReader();
                    reader.onloadend = () => this.form.photo = reader.result;

                    reader.readAsDataURL(file);
                } else {
                     Swal.fire({
                         type: "error",
                         title: "Oops...",
                         text: "File size cannot be more than 2mb"
                     });
                 }
            }
        }
    }
</script>
