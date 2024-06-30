@extends('site.partials.layout')
@section('content')
    <div class="container">
        <div class="card bg-light" style="max-width: 800px;  margin-right: auto; margin-left: auto;">
            <article class="card-body ">
                <h2 class="text-center" style="color:#6666cc;">Sign in</h2>
                <form method="POST" action="{{ route('site.company.login') }}">
                    @csrf
                    <fieldset>


                        <div class="mt-4 row-fluid">
                            <div class="control-group">

                                <label for="" class="control-label  mb-1" data-original-title=""> Email
                                </label>
                                <input type="email" name="email" size="20" maxlength="255"
                                    class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                    placeholder="Email" value="{{ old('email') }}">
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>
                        </div>
                        <div class="mt-4 row-fluid">
                            <div class="control-group">
                                <label for="" class=" control-label  mb-1" data-original-title="">Password </label>
                                <input type="password" name="password"
                                    class="form-control  text {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                    placeholder="Password" value="{{ old('password') }}">
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>
                        </div>


                    </fieldset>

                    <div class="mt-4 mb-4">
                        <input type="submit" value="sign up" class="btn btn-primary button" />
                    </div>

                </form>

            </article>
        </div>
        </br>
        </br>
    </div>
@endsection
