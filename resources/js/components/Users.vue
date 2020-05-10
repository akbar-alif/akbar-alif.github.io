<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdmin()">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Users list</h3>
                        <div class="card-tools">
                            <button class="btn btn-success" @click="openNewModal">
                                Add <i class="fa fa-user-plus fw"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Registered At</th>
                                    <th>Modify</th>
                                </tr>
                            </thead>
                            <tbody v-if="users.data && users.data.length > 0">
                                <tr v-for="user in users.data">
                                    <td>{{ user.id }}</td>
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.type | uc_first }}</td>
                                    <td>{{ user.created_at | formatted_date }}</td>
                                    <td>
                                        <a href="#" @click="openUpdateModal(user)">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        /
                                        <a href="#" @click="destroy(user.id)">
                                            <i class="fa fa-trash red"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td class="text-bold">Users not found!</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="users" class="m-2" @pagination-change-page="load"></pagination>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
        <div v-else>
            <not-found></not-found>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel" >{{ editMode ? 'Update user ' + user.name : 'Add user'}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateUser(user) : createUser()">
                        <div class="modal-body">
                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name" placeholder="Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.email" type="email" name="email" placeholder="Email address"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                            </div>

                            <div class="form-group">
                                <select name="type" v-model="form.type" id="type" class="form-control" :class="{
                                'is-invalid': form.errors.has('type') }">
                                    <option value="">Select user role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">Standard user</option>
                                    <option value="author">Author</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.password" type="password" name="password" id="password" placeholder="Password"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                            </div>

                            <div class="form-group">
                            <textarea v-model="form.bio" name="bio" id="bio" placeholder="Short user bio.. (Optional)"
                                      class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                <has-error :form="form" field="bio"></has-error>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" v-show="!editMode" class="btn btn-primary">Create</button>
                            <button type="submit" v-show="editMode" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

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
                users: {},
                user: {},
                editMode: false,
            }
        },

        methods: {
            createUser() {
                this.$Progress.start();

                this.form.post('api/user')
                .then(response => {
                    // this.load();
                    Fire.$emit('user-was-created');

                    toast.fire({
                        type: 'success',
                        title: 'User created successfully'
                    });

                    this.closeModal();

                    this.$Progress.finish();
                }).catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                });
            },

            load(page = 1) {
                let self = this;
                if(self.$gate.isAdmin()) {
                    axios.get('api/user?page=' + page)
                        .then(response => {
                            self.users = response.data.users;
                        }).catch(errors => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Could not fetch users.',
                        });
                    });
                }
            },

            destroy(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        axios.delete('api/user/' + id)
                            .then(response => {
                                Swal.fire(
                                    'Deleted!',
                                    'User has been deleted.',
                                    'success'
                                );

                                this.load();
                            }).catch(errors => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                        });
                    }
                });
            },

            updateUser(user) {
                this.form.put('api/user/' + user.id)
                .then(response => {
                    toast.fire({
                        type: 'success',
                        title: 'User updated successfully'
                    });

                    this.closeModal();
                    this.load();
                }).catch(error => {
                    console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                });
            },

            openNewModal() {
                this.editMode = false;
                this.user = {};
                this.form.reset();
                this.openModal();
            },

            openUpdateModal(user) {
                this.editMode = true;
                this.user = user;
                this.form.fill(user);
                this.openModal();
            },

            closeModal() {
                $('#addUserModal').modal('hide');
            },

            openModal() {
                $('#addUserModal').modal('show');
            },

    },

        created() {
            this.load();
            Fire.$on('user-was-created', () => {
                this.load();
            });

            Fire.$on('search', () => {
                axios.get('api/search?q=' + this.$parent.search)
                    .then(response => {
                        this.users = response.data.users;
                    }).catch(errors => {
                        console.log(errors);
                    })
            });
        }
    }
</script>
