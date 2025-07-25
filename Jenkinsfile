pipeline {
  agent any

  environment {
    // ---- AWS / ECR ----
    AWS_ACCOUNT_ID = '993178286287'
    AWS_REGION     = 'ap-south-1'
    ECR_REPO       = 'obcs-app'
    ECR_URI        = "${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}"

    // ---- Git / Repo ----
    REPO_URL       = 'https://github.com/kiranmohite2417/Online-Birth-Certificate-System-PHP.git'
    GITHUB_USER    = 'kiranmohite2417'   // change if needed
    MANIFEST_FILE  = 'k8s/obcs-all.yaml'

    // ---- Image Tag ----
    IMAGE_TAG      = "${env.BUILD_NUMBER}" // or: "${env.GIT_COMMIT.take(7)}"
  }

  options {
    timestamps()
  }

  stages {

    stage('Checkout') {
      steps {
        git branch: 'main', url: "${REPO_URL}"
      }
    }

    stage('AWS Login & Ensure ECR Repo') {
      steps {
        withCredentials([usernamePassword(credentialsId: 'aws-creds',
                                          usernameVariable: 'AWS_ACCESS_KEY_ID',
                                          passwordVariable: 'AWS_SECRET_ACCESS_KEY')]) {
          sh '''
            set -e
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
          set -e
          docker build -t ${ECR_URI}:${IMAGE_TAG} -t ${ECR_URI}:latest .
          docker push ${ECR_URI}:${IMAGE_TAG}
          docker push ${ECR_URI}:latest
        '''
      }
    }

    stage('Bump image tag in manifest & push back to Git') {
      steps {
        withCredentials([string(credentialsId: 'git-token', variable: 'GIT_TOKEN')]) {
          sh '''
            set -e
            sed -E -i "s|(${ECR_URI}:)([[:alnum:]._-]+)|\\1${IMAGE_TAG}|g" ${MANIFEST_FILE}

            git config user.email "jenkins@ci.local"
            git config user.name "jenkins"

            git add ${MANIFEST_FILE}
            git commit -m "CD: update image tag to ${IMAGE_TAG}" || echo "No changes to commit"

            git push https://${GITHUB_USER}:${GIT_TOKEN}@github.com/kiranmohite2417/Online-Birth-Certificate-System-PHP.git HEAD:main
          '''
        }
      }
    }
  }

  post {
    success {
      echo "✅ Build pushed as ${ECR_URI}:${IMAGE_TAG} and manifest updated. Argo CD will roll it out."
    }
    failure {
      echo "❌ Pipeline failed."
    }
  }
}

