pipeline {
  agent any

  environment {
    AWS_ACCOUNT_ID = '993178286287'
    AWS_REGION     = 'ap-south-1'
    ECR_REPO       = 'obcs-app'
    IMAGE_TAG      = "${env.BUILD_NUMBER}" // or "${env.GIT_COMMIT.take(7)}"
    ECR_URI        = "${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}"
    MANIFEST_FILE  = 'k8s/obcs-all.yaml'
  }

  options {
    ansiColor('xterm')
    timestamps()
  }

  stages {

    stage('Checkout') {
      steps {
        git branch: 'main', url: 'https://github.com/kiranmohite2417/Online-Birth-Certificate-System-PHP.git'
      }
    }

    stage('AWS Login & Ensure ECR Repo') {
      steps {
        withCredentials([usernamePassword(credentialsId: 'aws-creds',
                                          usernameVariable: 'AWS_ACCESS_KEY_ID',
                                          passwordVariable: 'AWS_SECRET_ACCESS_KEY')]) {
          sh '''
            export AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID
            export AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY
            export AWS_DEFAULT_REGION='${AWS_REGION}'

            aws ecr describe-repositories --repository-names ${ECR_REPO} >/dev/null 2>&1 \
              || aws ecr create-repository --repository-name ${ECR_REPO}

            aws ecr get-login-password --region ${AWS_REGION} \
              | docker login --username AWS --password-stdin ${ECR_URI}
          '''
        }
      }
    }

    stage('Build & Push Image') {
      steps {
        sh '''
          docker build -t ${ECR_URI}:${IMAGE_TAG} -t ${ECR_URI}:latest .
          docker push ${ECR_URI}:${IMAGE_TAG}
          docker push ${ECR_URI}:latest
        '''
      }
    }

    stage('Bump image tag in manifest & push back to Git') {
      steps {
        withCredentials([usernamePassword(credentialsId: 'git-cred',
                                          usernameVariable: 'GIT_USER',
                                          passwordVariable: 'GIT_TOKEN')]) {
          sh '''
            # Rewrite the tag in the manifest (change whatever tag is there to the new one)
            sed -E -i "s|(${ECR_URI}:)([[:alnum:]._-]+)|\\1${IMAGE_TAG}|g" ${MANIFEST_FILE}

            git config user.email "jenkins@ci.local"
            git config user.name "jenkins"

            git add ${MANIFEST_FILE}
            git commit -m "CD: update image tag to ${IMAGE_TAG}"
            git push https://${GIT_USER}:${GIT_TOKEN}@github.com/kiranmohite2417/Online-Birth-Certificate-System-PHP.git HEAD:main
          '''
        }
      }
    }
  }

  post {
    success {
      echo "Pushed image ${ECR_URI}:${IMAGE_TAG} and updated manifest. Argo CD will roll it out."
    }
    failure {
      echo "Pipeline failed."
    }
  }
}

