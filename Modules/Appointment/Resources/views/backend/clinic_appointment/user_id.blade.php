<div class="d-flex gap-3 align-items-center">
  <img src="{{ optional($data->user)->profile_image ?? default_user_avatar() }}" alt="avatar" class="avatar avatar-40 rounded-pill">
  <div class="text-start">
    <h6 class="m-0">{{ optional($data->user)->full_name ?? default_user_name() }}</h6>
    <span>{{ optional($data->user)->email ?? '--' }}</span>
  </div>
</div>
