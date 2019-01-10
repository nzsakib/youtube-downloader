<template>
	<div>
		<div class="row">
			<div class="col" v-if="isLoading">
				<div class="d-flex justify-content-center" >
					<div class="spinner-border" role="status">
					  <span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>
			<div class="mx-auto" v-else style="width: 60%;">
				<form action="" class="dl-form" method="POST" @submit.prevent="download">
					<input type="hidden" name="_token" :value="csrf">
					<div class="col-10">
						<input type="text" class="form-control" v-model="data.url" name="url" autofocus>
					
					</div>
					<button class="btn btn-primary col-2" :disabled="isLoading">Download</button>
				</form>
			</div>
		</div>

		<div class="row" v-if="hasLinks">
			<div class="col">
				<table class="table table-bordered results">
					<tr>
						<th></th>
						<th>Title</th>
						<th>Status</th>
						<th>Download</th>
					</tr>
					
					<tr v-for="video in videos">
						<td>
							<img :src="video.thumbnail" :alt="video.title" class="img-fluid" style="width: 200px;" v-if="video.status === 'finished'">
							<div class="spinner-border" role="status" v-else>
							  <span class="sr-only">Loading...</span>
							</div>
						</td>
						<td>
							<span v-if="video.status === 'finished'">{{ video.title }}</span>
							<div class="spinner-border" role="status" v-else>
							  <span class="sr-only">Loading...</span>
							</div>
						</td>
						<td>
							<div class="badge badge-primary text-wrap text-uppercase p-2">
								{{ video.status }}
							</div>
						</td>
						<td>
							<a :href="'/download/' + video.youtube_id" class="btn btn-info" target="__blank" v-if="video.status === 'finished'">Download</a>
							<div class="spinner-border" role="status" v-else>
							  <span class="sr-only">Loading...</span>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
	// import { validateUrl } from 'youtube-validate';

	export default {
		data() {
			return {
				data: {
					url: '',
					id: '',
				},
				hasLinks: false,
				isLoading: false,
				videos: [],
				csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
			};
		},

		methods: {
			download() {
				this.isLoading = true;
				let video_id = this.getVideoId();
				if( video_id ) {
					console.log(video_id);
					this.data.id = video_id;
					axios.post('/api/download', this.data)
						.then(response => {
							this.hasLinks = true;
							
							console.log('Download started');
							
							this.videos.push(response.data.history);
							console.log(response.data.history)
							this.isLoading = false;
							this.data.url = '';
							
						});
					Echo.channel('download-' + video_id)
						.listen('DownloadHasFinished', ({history}) => {
							this.videos.forEach(video => {
								if(video.id == history.id) {
									video.status = history.status;
									video.title = history.title;
									video.thumbnail = history.thumbnail;
									video.path = history.path;
								}
							});
							// this.videos.push(history);
							console.log('Download has finished behind the scences');
							// console.log(history);
							// this.isLoading = false;
						});
				}
				else {
					console.log("Errorrrr");
				}

			},
			getVideoId() {
				let url = this.data.url;
				// Check if the given link is from youtube 
				let video_id;
				if(url.includes("youtube.com") || url.includes("youtu.be")) {
					let rx = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
					video_id = url.match(rx)[1];


					return video_id;
				}

				return false;
			}
		},

		mounted() {

		}
	}
</script>

<style>
	.dl-form {
		width: 100%;
	}

	.results {
		margin-top: 30px;
	}
</style>